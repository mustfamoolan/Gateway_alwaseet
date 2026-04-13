const { 
    default: makeWASocket, 
    useMultiFileAuthState, 
    DisconnectReason, 
    fetchLatestBaileysVersion, 
    makeCacheableSignalKeyStore 
} = require('@whiskeysockets/baileys');
const express = require('express');
const cors = require('cors');
const qrcode = require('qrcode');
const pino = require('pino');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(cors());
app.use(express.json());

const logger = pino({ level: 'info' });
const sessions = new Map();

// --- WhatsApp Engine Logic ---

async function startSession(sessionId) {
    console.log(`[${sessionId}] Starting session initialization...`);
    if (sessions.has(sessionId)) {
        console.log(`[${sessionId}] Session already exists in memory.`);
        return sessions.get(sessionId);
    }

    const sessionPath = path.join(__dirname, 'auth', sessionId);
    console.log(`[${sessionId}] Loading auth state from: ${sessionPath}`);
    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    
    console.log(`[${sessionId}] Fetching latest Baileys version...`);
    const { version } = await fetchLatestBaileysVersion().catch(err => {
        console.error(`[${sessionId}] Error fetching Baileys version:`, err.message);
        return { version: [2, 3000, 1015901307] }; // Fallback version
    });
    console.log(`[${sessionId}] Using Baileys version: ${version.join('.')}`);

    const sock = makeWASocket({
        version,
        auth: {
            creds: state.creds,
            keys: makeCacheableSignalKeyStore(state.keys, logger),
        },
        printQRInTerminal: false,
        logger,
    });

    const sessionObj = {
        sock,
        qr: null,
        status: 'pending',
    };
    sessions.set(sessionId, sessionObj);

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log(`[${sessionId}] New QR code generated.`);
            sessionObj.qr = await qrcode.toDataURL(qr);
            sessionObj.status = 'pending';
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
            console.log(`[${sessionId}] Connection closed. Reason:`, lastDisconnect.error?.message, '| Reconnecting:', shouldReconnect);
            
            if (shouldReconnect) {
                sessions.delete(sessionId);
                startSession(sessionId);
            } else {
                console.log(`[${sessionId}] Logged out. Cleaning up...`);
                sessions.delete(sessionId);
                fs.rmSync(sessionPath, { recursive: true, force: true });
            }
        } else if (connection === 'open') {
            console.log(`[${sessionId}] Connection opened successfully!`);
            sessionObj.status = 'connected';
            sessionObj.qr = null;
        }
    });

    return sessionObj;
}

// --- API Endpoints ---

// Get QR Code / Session Status
app.get('/session/:id', async (req, res) => {
    const sessionId = req.params.id;
    let session = sessions.get(sessionId);

    if (!session) {
        session = await startSession(sessionId);
    }

    res.json({
        id: sessionId,
        status: session.status,
        qr: session.qr
    });
});

// Send Message
app.post('/message/send', async (req, res) => {
    const { sessionId, to, text } = req.body;
    const session = sessions.get(sessionId);

    if (!session || session.status !== 'connected') {
        return res.status(400).json({ error: 'Session not connected' });
    }

    try {
        const jid = to.includes('@s.whatsapp.net') ? to : `${to}@s.whatsapp.net`;
        const result = await session.sock.sendMessage(jid, { text });
        res.json({ success: true, result });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Delete Session (Logout)
app.delete('/session/:id', async (req, res) => {
    const sessionId = req.params.id;
    const session = sessions.get(sessionId);

    if (session) {
        await session.sock.logout();
        sessions.delete(sessionId);
    }
    
    const sessionPath = path.join(__dirname, 'auth', sessionId);
    if (fs.existsSync(sessionPath)) {
        fs.rmSync(sessionPath, { recursive: true, force: true });
    }

    res.json({ success: true });
});

const PORT = process.env.WHATSAPP_PORT || 3000;
app.listen(PORT, () => {
    console.log(`WhatsApp Engine running on port ${PORT}`);
});
