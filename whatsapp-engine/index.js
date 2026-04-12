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
    if (sessions.has(sessionId)) return sessions.get(sessionId);

    const sessionPath = path.join(__dirname, 'auth', sessionId);
    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    const { version } = await fetchLatestBaileysVersion();

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
            sessionObj.qr = await qrcode.toDataURL(qr);
            sessionObj.status = 'pending';
        }

        if (connection === 'close') {
            const shouldReconnect = lastDisconnect.error?.output?.statusCode !== DisconnectReason.loggedOut;
            console.log('Connection closed due to ', lastDisconnect.error, ', reconnecting ', shouldReconnect);
            
            if (shouldReconnect) {
                sessions.delete(sessionId);
                startSession(sessionId);
            } else {
                sessions.delete(sessionId);
                // Clean up files
                fs.rmSync(sessionPath, { recursive: true, force: true });
            }
        } else if (connection === 'open') {
            console.log('Opened connection');
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
