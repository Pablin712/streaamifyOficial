/**
 * Session Manager para Chat Anónimo
 * Maneja sesiones temporales de 20 horas usando localStorage
 */
class SessionManager {
  constructor() {
    this.storageKey = 'streamify_chat_session';
    this.sessionDuration = 20 * 60 * 60 * 1000; // 20 horas en ms
  }

  /**
   * Inicializar sesión anónima
   */
  async init() {
    const existingSession = this.getSession();

    // Si existe sesión válida, retornarla
    if (existingSession && !this.isExpired(existingSession)) {
      return existingSession;
    }

    // Crear nueva sesión
    const newSession = await this.createSession();
    this.saveSession(newSession);
    return newSession;
  }

  /**
   * Crear nueva sesión con fingerprint simple
   */
  async createSession() {
    // Fingerprint simple basado en navegador
    const fingerprint = this.generateFingerprint();

    const sessionId = `anon_${fingerprint}_${this.generateHash()}`;
    const createdAt = Date.now();
    const expiresAt = createdAt + this.sessionDuration;

    return {
      sessionId,
      fingerprint,
      createdAt,
      expiresAt,
    };
  }

  /**
   * Generar fingerprint simple del navegador
   */
  generateFingerprint() {
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    ctx.textBaseline = 'top';
    ctx.font = '14px Arial';
    ctx.fillText('fingerprint', 2, 2);

    const canvasData = canvas.toDataURL();

    // Combinar varios datos del navegador
    const data = [
      navigator.userAgent,
      navigator.language,
      screen.colorDepth,
      screen.width + 'x' + screen.height,
      new Date().getTimezoneOffset(),
      canvasData.slice(0, 100)
    ].join('|');

    return this.simpleHash(data);
  }

  /**
   * Hash simple (no criptográfico, solo para fingerprint)
   */
  simpleHash(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
      const char = str.charCodeAt(i);
      hash = ((hash << 5) - hash) + char;
      hash = hash & hash; // Convert to 32bit integer
    }
    return Math.abs(hash).toString(36);
  }

  /**
   * Generar hash aleatorio
   */
  generateHash() {
    return Math.random().toString(36).substring(2, 15) +
           Math.random().toString(36).substring(2, 15);
  }

  /**
   * Guardar sesión en localStorage
   */
  saveSession(session) {
    localStorage.setItem(this.storageKey, JSON.stringify(session));
  }

  /**
   * Obtener sesión actual
   */
  getSession() {
    const data = localStorage.getItem(this.storageKey);
    return data ? JSON.parse(data) : null;
  }

  /**
   * Obtener solo el ID de sesión
   */
  getSessionId() {
    const session = this.getSession();
    return session ? session.sessionId : null;
  }

  /**
   * Verificar si sesión está expirada
   */
  isExpired(session) {
    return Date.now() > session.expiresAt;
  }

  /**
   * Obtener tiempo de expiración
   */
  getExpirationTime() {
    const session = this.getSession();
    if (!session) return null;

    const remainingTime = session.expiresAt - Date.now();
    const hours = Math.floor(remainingTime / (1000 * 60 * 60));
    const minutes = Math.floor((remainingTime % (1000 * 60 * 60)) / (1000 * 60));

    return { hours, minutes, timestamp: session.expiresAt };
  }

  /**
   * Limpiar sesión expirada
   */
  clearSession() {
    localStorage.removeItem(this.storageKey);
  }

  /**
   * Renovar sesión (extender duración)
   */
  renewSession() {
    const session = this.getSession();
    if (session) {
      session.expiresAt = Date.now() + this.sessionDuration;
      this.saveSession(session);
    }
  }
}

export default SessionManager;
