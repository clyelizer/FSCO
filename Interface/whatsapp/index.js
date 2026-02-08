require('dotenv').config();
const { makeWASocket, useMultiFileAuthState, DisconnectReason } = require('@whiskeysockets/baileys');
const express = require('express');
const cors = require('cors');
const axios = require('axios');
const fs = require('fs');
const path = require('path');

// Configuration
const app = express();
app.use(cors());
app.use(express.json());

// Configuration de l'IA
const AI_TYPE = process.env.AI_TYPE || 'gemini';
const PHP_API_URL = process.env.PHP_API_URL || 'https://fsco.gt.tc/Interface/api';
const PHP_API_KEY = process.env.PHP_API_KEY || 'fsco_change_this_to_secure_key';
const AUTHORIZED_NUMBERS = (process.env.AUTHORIZED_NUMBERS || '').split(',').map(n => n.trim());
const DEBUG = process.env.DEBUG === 'true';

// Logs
const logDir = path.join(__dirname, 'logs');
if (!fs.existsSync(logDir)) {
    fs.mkdirSync(logDir, { recursive: true });
}

function log(message, type = 'info') {
    const timestamp = new Date().toISOString();
    const logMessage = `[${timestamp}] [${type.toUpperCase()}] ${message}\n`;
    fs.appendFileSync(path.join(logDir, 'whatsapp.log'), logMessage);
    if (DEBUG) console.log(logMessage.trim());
}

// Client IA
class AIClient {
    constructor() {
        this.type = AI_TYPE;
        this.config = this.getConfig();
    }

    getConfig() {
        switch (this.type) {
            case 'openai':
                return {
                    apiKey: process.env.OPENAI_API_KEY,
                    model: process.env.OPENAI_MODEL || 'gpt-4',
                    temperature: parseFloat(process.env.OPENAI_TEMPERATURE || '0.7'),
                    maxTokens: parseInt(process.env.OPENAI_MAX_TOKENS || '2000')
                };
            case 'anthropic':
                return {
                    apiKey: process.env.ANTHROPIC_API_KEY,
                    model: process.env.ANTHROPIC_MODEL || 'claude-3-sonnet-20240229',
                    temperature: parseFloat(process.env.ANTHROPIC_TEMPERATURE || '0.7'),
                    maxTokens: parseInt(process.env.ANTHROPIC_MAX_TOKENS || '2000')
                };
            case 'local':
                return {
                    url: process.env.LOCAL_AI_URL || 'http://localhost:11434',
                    model: process.env.LOCAL_AI_MODEL || 'llama2',
                    temperature: parseFloat(process.env.LOCAL_AI_TEMPERATURE || '0.7'),
                    maxTokens: parseInt(process.env.LOCAL_AI_MAX_TOKENS || '2000')
                };
            case 'gemini':
                return {
                    apiKey: process.env.GEMINI_API_KEY,
                    model: process.env.GEMINI_MODEL || 'gemini-1.5-flash', // Gemini 2.0 Flash n'est peut-être pas encore dispo partout
                    temperature: parseFloat(process.env.GEMINI_TEMPERATURE || '0.7'),
                    maxTokens: parseInt(process.env.GEMINI_MAX_TOKENS || '2048')
                };
            default:
                throw new Error(`Type d'IA non supporté: ${this.type}`);
        }
    }

    async chat(messages, context = {}) {
        try {
            let response;

            switch (this.type) {
                case 'openai':
                    response = await this.chatOpenAI(messages, context);
                    break;
                case 'anthropic':
                    response = await this.chatAnthropic(messages, context);
                    break;
                case 'local':
                    response = await this.chatLocal(messages, context);
                    break;
                case 'gemini':
                    response = await this.chatGemini(messages, context);
                    break;
            }

            return response;
        } catch (error) {
            log(`Erreur IA: ${error.message}`, 'error');
            throw error;
        }
    }

    async chatOpenAI(messages, context) {
        const { OpenAI } = require('openai');
        const openai = new OpenAI({ apiKey: this.config.apiKey });

        const systemMessage = {
            role: 'system',
            content: `Tu es un assistant IA pour le site FSCO. 
            Contexte du site: ${JSON.stringify(context)}
            
            Règles:
            - Réponds en français
            - Sois concis et professionnel
            - Pour les actions sur le site, utilise le format JSON
            - Demande confirmation avant d'appliquer les changements`
        };

        const response = await openai.chat.completions.create({
            model: this.config.model,
            messages: [systemMessage, ...messages],
            temperature: this.config.temperature,
            max_tokens: this.config.maxTokens
        });

        return response.choices[0].message.content;
    }

    async chatAnthropic(messages, context) {
        const Anthropic = require('@anthropic-ai/sdk');
        const anthropic = new Anthropic({ apiKey: this.config.apiKey });

        const systemMessage = `Tu es un assistant IA pour le site FSCO. 
        Contexte du site: ${JSON.stringify(context)}
        
        Règles:
        - Réponds en français
        - Sois concis et professionnel
        - Pour les actions sur le site, utilise le format JSON
        - Demande confirmation avant d'appliquer les changements`;

        const response = await anthropic.messages.create({
            model: this.config.model,
            max_tokens: this.config.maxTokens,
            system: systemMessage,
            messages: messages,
            temperature: this.config.temperature
        });

        return response.content[0].text;
    }

    async chatLocal(messages, context) {
        const systemMessage = {
            role: 'system',
            content: `Tu es un assistant IA pour le site FSCO. 
            Contexte du site: ${JSON.stringify(context)}
            
            Règles:
            - Réponds en français
            - Sois concis et professionnel
            - Pour les actions sur le site, utilise le format JSON
            - Demande confirmation avant d'appliquer les changements`
        };

        const response = await axios.post(`${this.config.url}/api/chat`, {
            model: this.config.model,
            messages: [systemMessage, ...messages],
            stream: false
        });

        return response.data.message.content;
    }

    async chatGemini(messages, context) {
        const systemMessage = `Tu es un assistant IA pour le site FSCO. 
        Contexte du site: ${JSON.stringify(context)}
        
        Règles:
        - Réponds en français
        - Sois concis et professionnel
        - Pour les actions sur le site, utilise le format JSON (dans un bloc \`\`\`json)
        - Demande confirmation avant d'appliquer les changements`;

        // Formater l'historique pour Gemini
        let history = messages.map(m => `${m.role === 'user' ? 'User' : 'AI'}: ${m.content}`).join("\n");
        const prompt = systemMessage + "\n\n" + history + "\nAI:";

        const response = await axios.post(
            `https://generativelanguage.googleapis.com/v1beta/models/${this.config.model}:generateContent?key=${this.config.apiKey}`,
            {
                contents: [{ parts: [{ text: prompt }] }],
                generationConfig: {
                    temperature: this.config.temperature,
                    maxOutputTokens: this.config.maxTokens
                }
            }
        );

        if (response.data.candidates && response.data.candidates[0].content) {
            return response.data.candidates[0].content.parts[0].text;
        }
        throw new Error("Réponse vide de Gemini");
    }
}

// Initialisation de l'IA
const aiClient = new AIClient();

// Gestion de l'état d'authentification WhatsApp
const authStatePath = path.join(__dirname, 'auth', 'session.json');
if (!fs.existsSync(path.join(__dirname, 'auth'))) {
    fs.mkdirSync(path.join(__dirname, 'auth'), { recursive: true });
}

// Initialisation de Baileys
const sock = makeWASocket({
    authState: useMultiFileAuthState(authStatePath),
    printQRInTerminal: true,
    defaultQueryTimeoutMs: undefined,
    logger: {
        level: DEBUG ? 'debug' : 'error',
        stream: {
            write: (message) => log(message, 'whatsapp')
        }
    }
});

// Événements WhatsApp
sock.ev.on('connection.update', async ({ connection, lastDisconnect, isNewConnection }) => {
    if (connection === 'open') {
        log('Connexion WhatsApp établie', 'success');
        await notifyPHP('whatsapp_connected', { status: 'connected' });
    } else if (connection === 'close') {
        log(`Connexion WhatsApp fermée: ${lastDisconnect?.reason}`, 'warning');
        await notifyPHP('whatsapp_disconnected', { reason: lastDisconnect?.reason });
    }
});

sock.ev.on('creds.update', async () => {
    log('Credentials mis à jour', 'info');
    await notifyPHP('whatsapp_credentials_updated', {});
});

sock.ev.on('messages.upsert', async ({ messages, type }) => {
    if (type !== 'notify') return;

    for (const msg of messages) {
        if (!msg.key.fromMe) {
            await handleIncomingMessage(msg);
        }
    }
});

// Gestion des messages entrants
async function handleIncomingMessage(msg) {
    const phoneNumber = msg.key.remoteJid;
    const message = msg.message?.conversation || msg.message?.extendedTextMessage?.text || '';

    // Vérifier si le numéro est autorisé
    if (AUTHORIZED_NUMBERS.length > 0 && !isAuthorized(phoneNumber)) {
        log(`Message non autorisé de ${phoneNumber}`, 'warning');
        return;
    }

    log(`Message reçu de ${phoneNumber}: ${message}`, 'info');

    try {
        // Envoyer à l'IA
        const response = await processMessageWithAI(message, phoneNumber);

        // Envoyer la réponse
        await sock.sendMessage(phoneNumber, { text: response });

        log(`Réponse envoyée à ${phoneNumber}`, 'success');
    } catch (error) {
        log(`Erreur traitement message: ${error.message}`, 'error');
        await sock.sendMessage(phoneNumber, {
            text: '❌ Désolé, une erreur est survenue. Veuillez réessayer.'
        });
    }
}

function isAuthorized(phoneNumber) {
    const normalizedNumber = phoneNumber.replace(/[^0-9]/g, '');
    return AUTHORIZED_NUMBERS.some(auth => {
        const normalizedAuth = auth.replace(/[^0-9]/g, '');
        return normalizedNumber.includes(normalizedAuth) || normalizedAuth.includes(normalizedNumber);
    });
}

// Traitement du message avec l'IA
async function processMessageWithAI(message, phoneNumber) {
    // Récupérer l'historique des messages
    const history = await getMessageHistory(phoneNumber);

    // Ajouter le nouveau message
    history.push({ role: 'user', content: message });

    // Récupérer le contexte du site
    const context = await getSiteContext();

    // Envoyer à l'IA
    const response = await aiClient.chat(history, context);

    // Analyser la réponse
    const parsedResponse = parseAIResponse(response);

    // Si c'est une action sur le site, créer une demande
    if (parsedResponse.action) {
        const requestId = await createRequest(parsedResponse.action, phoneNumber);

        // Notifier l'interface PHP
        await notifyPHP('new_request', {
            request_id: requestId,
            action: parsedResponse.action,
            phone_number: phoneNumber
        });

        return `✅ Demande créée: ${requestId}\n\n${parsedResponse.message}\n\nRépondez "Ok, applique" pour confirmer ou "Annuler" pour annuler.`;
    }

    return parsedResponse.message || response;
}

// Parser la réponse de l'IA
function parseAIResponse(response) {
    // Chercher un bloc JSON dans la réponse
    const jsonMatch = response.match(/```json\n([\s\S]*?)\n```/);

    if (jsonMatch) {
        try {
            const actionData = JSON.parse(jsonMatch[1]);
            return {
                action: actionData,
                message: response.replace(jsonMatch[0], '').trim()
            };
        } catch (error) {
            log(`Erreur parsing JSON: ${error.message}`, 'error');
        }
    }

    return { message: response };
}

// Créer une demande
async function createRequest(action, phoneNumber) {
    const requestId = `REQ-${new Date().getFullYear()}-${String(Date.now()).slice(-3)}`;

    const requestData = {
        id: requestId,
        type: action.type,
        status: 'pending_confirmation',
        created_at: new Date().toISOString(),
        created_by: phoneNumber,
        data: action.data,
        validation: {
            validated_by: null,
            validated_at: null,
            comments: ''
        }
    };

    // Sauvegarder dans l'interface PHP
    await notifyPHP('create_request', requestData);

    return requestId;
}

// Récupérer le contexte du site
async function getSiteContext() {
    try {
        const response = await axios.get(`${PHP_API_URL}/context.php`, {
            headers: { 'X-API-Key': PHP_API_KEY }
        });
        return response.data;
    } catch (error) {
        log(`Erreur récupération contexte: ${error.message}`, 'error');
        return {};
    }
}

// Récupérer l'historique des messages
async function getMessageHistory(phoneNumber) {
    try {
        const response = await axios.get(`${PHP_API_URL}/history.php?wa_id=${phoneNumber}`, {
            headers: { 'X-API-Key': PHP_API_KEY }
        });
        return response.data.history || [];
    } catch (error) {
        return [];
    }
}

// Notifier l'interface PHP
async function notifyPHP(event, data) {
    try {
        await axios.post(`${PHP_API_URL}/webhook.php`, {
            event,
            data,
            timestamp: new Date().toISOString()
        }, {
            headers: {
                'X-API-Key': PHP_API_KEY,
                'Content-Type': 'application/json'
            }
        });
        log(`Notification envoyée à PHP: ${event}`, 'info');
    } catch (error) {
        log(`Erreur notification PHP: ${error.message}`, 'error');
    }
}

// API Express pour communiquer avec l'interface PHP
app.post('/api/message', async (req, res) => {
    try {
        const { phone_number, message } = req.body;

        if (!phone_number || !message) {
            return res.status(400).json({ error: 'phone_number et message requis' });
        }

        await sock.sendMessage(phone_number, { text: message });

        res.json({ success: true, message: 'Message envoyé' });
    } catch (error) {
        log(`Erreur envoi message: ${error.message}`, 'error');
        res.status(500).json({ error: error.message });
    }
});

app.get('/api/status', (req, res) => {
    const state = sock.user ? 'connected' : 'disconnected';
    res.json({
        status: state,
        ai_type: AI_TYPE,
        ai_model: aiClient.config.model || aiClient.config.url
    });
});

app.get('/api/qr', (req, res) => {
    // Le QR code est affiché dans le terminal
    res.json({ message: 'QR code disponible dans le terminal' });
});

// Démarrage du serveur
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    log(`Service WhatsApp démarré sur le port ${PORT}`, 'success');
    log(`Type d'IA: ${AI_TYPE}`, 'info');
    log(`URL API PHP: ${PHP_API_URL}`, 'info');
});

// Gestion de l'arrêt
process.on('SIGINT', async () => {
    log('Arrêt du service...', 'info');
    await sock.logout();
    process.exit(0);
});
