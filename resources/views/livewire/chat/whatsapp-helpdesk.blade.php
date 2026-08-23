<div class="wa-helpdesk wa-pane-{{ $mobilePane }}" data-pane="{{ $mobilePane }}" wire:poll.3s="refreshChat">
    <style>
        /* ================================================
           DESIGN SYSTEM PREMIUM - INTERCOM / ZENDESK STYLE
           ================================================ */

        :root {
            --wa-space-0: 0px;
            --wa-space-1: 4px;
            --wa-space-2: 8px;
            --wa-space-3: 12px;
            --wa-space-4: 16px;
            --wa-space-5: 20px;
            --wa-space-6: 24px;
            --wa-space-7: 32px;

            --wa-radius-sm: 6px;
            --wa-radius-md: 10px;
            --wa-radius-lg: 14px;
            --wa-radius-xl: 20px;
            --wa-radius-full: 9999px;

            --wa-shadow-sm: 0 1px 2px rgba(0,0,0,0.04);
            --wa-shadow-md: 0 2px 8px rgba(0,0,0,0.06);
            --wa-shadow-lg: 0 4px 16px rgba(0,0,0,0.08);

            --wa-transition: 160ms cubic-bezier(0.2, 0, 0.38, 0.9);

            --wa-bg: #f8fafc;
            --wa-panel: #ffffff;
            --wa-border: #e2e8f0;
            --wa-border-hover: #cbd5e1;
            --wa-text: #0f172a;
            --wa-text-secondary: #64748b;
            --wa-text-tertiary: #94a3b8;
            --wa-accent: #2563eb;
            --wa-accent-hover: #1d4ed8;
            --wa-accent-soft: #eff6ff;
            --wa-success: #10b981;
            --wa-success-soft: #ecfdf5;
            --wa-warning: #f59e0b;
            --wa-danger: #ef4444;
            --wa-danger-soft: #fef2f2;
            --wa-whatsapp: #128c7e;
            --wa-whatsapp-soft: #dcfce7;
        }

        :root[data-dark-mode="true"] {
            --wa-bg: #0f131a;
            --wa-panel: #171c24;
            --wa-border: #2a3240;
            --wa-border-hover: #3a4658;
            --wa-text: #edf2f7;
            --wa-text-secondary: #b7c2d0;
            --wa-text-tertiary: #8ea0b8;
            --wa-accent: #60a5fa;
            --wa-accent-hover: #3b82f6;
            --wa-accent-soft: #1e293b;
            --wa-success: #34d399;
            --wa-success-soft: #13302a;
            --wa-warning: #d6a83f;
            --wa-danger: #f87171;
            --wa-danger-soft: #3b1f24;
            --wa-whatsapp: #2dd4bf;
            --wa-whatsapp-soft: #12382f;

            --wa-shadow-sm: 0 1px 2px rgba(0,0,0,0.35);
            --wa-shadow-md: 0 2px 8px rgba(0,0,0,0.45);
            --wa-shadow-lg: 0 8px 20px rgba(0,0,0,0.6);
        }

        * {
            box-sizing: border-box;
        }

        .wa-helpdesk {
            height: calc(100dvh - 64px);
            max-height: calc(100dvh - 64px);
            min-height: 0;
            display: grid;
            grid-template-columns: minmax(320px, 380px) minmax(480px, 1fr) minmax(280px, 360px);
            background: var(--wa-bg);
            color: var(--wa-text);
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
            overflow: hidden;
        }

        .wa-column {
            min-height: 0;
            background: var(--wa-panel);
            border-right: 1px solid var(--wa-border);
            display: flex;
            flex-direction: column;
        }

        .wa-right {
            border-right: 0;
            background: var(--wa-bg);
        }

        .wa-toolbar {
            padding: 12px;
            border-bottom: 1px solid var(--wa-border);
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: var(--wa-panel);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* ================================================
           HEADER WHATSAPP PREMIUM
           ================================================ */

        .wa-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--wa-space-1);
        }

        .wa-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--wa-text);
            display: flex;
            align-items: center;
            gap: var(--wa-space-2);
        }

        .wa-channel-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            background: var(--wa-whatsapp-soft);
            color: #166534;
            border-radius: var(--wa-radius-full);
            font-size: 12px;
            font-weight: 600;
        }

        :root[data-dark-mode="true"] .wa-channel-badge {
            color: #9ff3d7;
        }

        .wa-header-actions {
            display: flex;
            gap: var(--wa-space-2);
            align-items: center;
        }

        .wa-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--wa-radius-md);
            border: 0;
            background: transparent;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--wa-text-secondary);
            transition: var(--wa-transition);
            font-size: 16px;
        }

        .wa-icon-btn:hover {
            background: var(--wa-bg);
            color: var(--wa-text);
        }

        /* ================================================
           BUSCADOR Y FILTROS MODERNO
           ================================================ */

        .wa-search {
            width: 100%;
            border: 1px solid var(--wa-border);
            border-radius: var(--wa-radius-md);
            padding: 10px 14px 10px 40px;
            color: var(--wa-text);
            background: var(--wa-bg);
            font-size: 14px;
            transition: var(--wa-transition);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
            background-size: 18px;
        }

        .wa-search:focus {
            outline: none;
            border-color: var(--wa-accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background-color: var(--wa-panel);
        }

        .wa-search::placeholder {
            color: var(--wa-text-tertiary);
        }

        .wa-filters {
            display: flex;
            gap: var(--wa-space-1);
            padding: 2px;
            background: var(--wa-bg);
            border-radius: var(--wa-radius-md);
            overflow-x: auto;
            scrollbar-width: none;
        }

        .wa-filters::-webkit-scrollbar {
            display: none;
        }

        .wa-etiqueta-filters {
            margin-top: 6px;
        }

        .wa-filter {
            padding: 7px 12px;
            border-radius: var(--wa-radius-sm);
            border: 0;
            background: transparent;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--wa-text-secondary);
            transition: var(--wa-transition);
            white-space: nowrap;
        }

        .wa-filter:hover {
            color: var(--wa-text);
            background: rgba(0,0,0,0.03);
        }

        :root[data-dark-mode="true"] .wa-filter:hover {
            background: rgba(148,163,184,0.14);
        }

        .wa-filter.active {
            background: var(--wa-panel);
            color: var(--wa-accent);
            font-weight: 600;
            box-shadow: var(--wa-shadow-sm);
        }

        .wa-channel-meta {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .wa-channel-dot {
            width: 9px;
            height: 9px;
            border-radius: 999px;
            flex: 0 0 auto;
            box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.04);
        }

        .wa-channel-dot.verde {
            background: #10b981;
        }

        .wa-channel-dot.azul {
            background: #2563eb;
        }

        .wa-channel-dot.naranja {
            background: #f97316;
        }

        .wa-channel-dot.otro {
            background: #94a3b8;
        }

        .wa-channel-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: var(--wa-text-secondary);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        :root[data-dark-mode="true"] .wa-channel-label {
            background: #243041;
            color: #d4deea;
        }

        /* ================================================
           LISTA CONVERSACIONES INBOX
           ================================================ */

        .wa-list {
            overflow-y: auto;
            min-height: 0;
            flex: 1;
        }

        .wa-item {
            width: 100%;
            text-align: left;
            border: 0;
            border-bottom: 1px solid var(--wa-border);
            background: var(--wa-panel);
            padding: 10px 12px;
            cursor: pointer;
            transition: var(--wa-transition);
            position: relative;
        }

        .wa-item:hover {
            background: var(--wa-bg);
        }

        .wa-item.active {
            background: var(--wa-accent-soft);
            border-left: 3px solid var(--wa-accent);
            box-shadow: var(--wa-shadow-md);
        }

        .wa-item.pinned {
            background: var(--wa-accent-soft);
        }

        .wa-item-wrapper {
            position: relative;
        }

        .wa-pin-btn {
            position: absolute;
            top: 8px;
            right: 10px;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 0;
            background: var(--wa-panel);
            box-shadow: var(--wa-shadow-sm);
            cursor: pointer;
            font-size: 11px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--wa-transition);
        }

        .wa-item-wrapper:hover .wa-pin-btn {
            opacity: 1;
        }

        .wa-pin-btn.active {
            opacity: 1;
            background: var(--wa-accent-soft);
        }

        .wa-item-row {
            display: flex;
            justify-content: space-between;
            gap: var(--wa-space-2);
            align-items: flex-start;
        }

        .wa-avatar {
            width: 34px;
            height: 34px;
            border-radius: var(--wa-radius-full);
            background: var(--wa-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 13px;
            color: var(--wa-text-secondary);
            flex-shrink: 0;
        }

        .wa-conversation-content {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .wa-name {
            font-weight: 600;
            color: var(--wa-text);
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .wa-number {
            color: var(--wa-text-tertiary);
            font-size: 12px;
        }

        .wa-preview {
            color: var(--wa-text-secondary);
            font-size: 12px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-top: 1px;
        }

        .wa-time {
            color: var(--wa-text-tertiary);
            font-size: 11px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .wa-meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: var(--wa-space-2);
            gap: var(--wa-space-2);
        }

        .wa-badge {
            display: inline-flex;
            align-items: center;
            min-height: 20px;
            padding: 2px 8px;
            border-radius: var(--wa-radius-full);
            background: var(--wa-bg);
            color: var(--wa-text-secondary);
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
        }

        .wa-badge.success { background: var(--wa-success-soft); color: #065f46; }
        .wa-badge.danger { background: var(--wa-danger); color: white; }
        .wa-badge.info { background: var(--wa-accent-soft); color: #1e40af; }
        .wa-badge.warning { background: #fef3c7; color: #92400e; }
        .wa-badge.muted { background: #e2e8f0; color: #334155; }
        .wa-badge.bot { background: #f3e8ff; color: #7e22ce; }
        .wa-badge.outline { background: transparent; border: 1px solid var(--wa-border); color: var(--wa-text-secondary); }
        .wa-badge.soporte       { background: #ef4444; color: white; }
        .wa-badge.cobrar        { background: #fef3c7; color: #92400e; }
        .wa-badge.quitar        { background: #fed7aa; color: #9a3412; }
        .wa-badge.cuenta-caida  { background: #fca5a5; color: #7f1d1d; }
        .wa-badge.renovar       { background: #bfdbfe; color: #1e3a8a; }
        .wa-badge.caida-pro     { background: #fde68a; color: #78350f; }

        .wa-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
        }

        .wa-operator-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 8px;
            border-radius: 999px;
            border: 1px solid var(--wa-border);
            background: #f8fafc;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            max-width: 65%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wa-operator-chip::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 999px;
            background: #94a3b8;
            flex: 0 0 auto;
        }

        /* ================================================
           CHAT CENTRAL
           ================================================ */

        .wa-chat {
            min-height: 0;
            display: flex;
            flex-direction: column;
            background: var(--wa-bg) !important;
        }

        .wa-chat-header {
            background: var(--wa-panel);
            border-bottom: 1px solid var(--wa-border);
            padding: var(--wa-space-3) var(--wa-space-4);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .wa-chat-title-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: var(--wa-space-3);
        }

        .wa-chat-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .wa-chat-title {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: var(--wa-text);
        }

        .wa-chat-subtitle {
            font-size: 13px;
            color: var(--wa-text-secondary);
        }

        .wa-chat-actions {
            display: flex;
            gap: var(--wa-space-2);
            flex-wrap: wrap;
        }

        .wa-chat-search {
            margin-top: 14px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .wa-chat-search-meta {
            font-size: 11px;
            color: var(--wa-text-tertiary);
            font-weight: 700;
        }

        .wa-load-older {
            align-self: center;
            border: 1px solid var(--wa-border);
            background: #fff;
            color: var(--wa-text-secondary);
            border-radius: var(--wa-radius-full);
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--wa-transition);
        }

        .wa-load-older:hover {
            border-color: var(--wa-accent);
            color: var(--wa-accent);
        }

        .wa-messages-meta {
            align-self: center;
            color: var(--wa-text-tertiary);
            font-size: 11px;
            margin-top: -4px;
        }

        .wa-highlight {
            background: #fde68a;
            color: #78350f;
            padding: 0 2px;
            border-radius: 4px;
        }

        .wa-action {
            padding: 8px 14px;
            border-radius: var(--wa-radius-md);
            border: 1px solid var(--wa-border);
            background: var(--wa-panel);
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--wa-text);
            transition: var(--wa-transition);
        }

        .wa-action:hover {
            border-color: var(--wa-border-hover);
            background: var(--wa-bg);
        }

        .wa-action.primary {
            background: var(--wa-accent);
            border-color: var(--wa-accent);
            color: white;
        }

        .wa-action.primary:hover {
            background: var(--wa-accent-hover);
            border-color: var(--wa-accent-hover);
        }

        .wa-select {
            padding: 8px 12px;
            border-radius: var(--wa-radius-md);
            border: 1px solid var(--wa-border);
            background: var(--wa-panel);
            cursor: pointer;
            font-size: 13px;
            min-width: 160px;
        }

        /* ================================================
           MENSAJES Y BURBUJAS
           ================================================ */

        .wa-messages {
            flex: 1;
            overflow-y: auto;
            padding: var(--wa-space-5) var(--wa-space-6);
            display: flex;
            flex-direction: column;
            gap: var(--wa-space-2);
            background: var(--wa-bg);
        }

        .wa-message {
            max-width: 65%;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .wa-message.cliente {
            align-self: flex-start;
        }

        .wa-message.empleado {
            align-self: flex-end;
        }

        .wa-message.ia {
            align-self: flex-end;
        }

        .wa-message.sistema {
            align-self: center;
            max-width: 85%;
        }

        .wa-bubble {
            position: relative;
            border-radius: var(--wa-radius-lg);
            padding: 10px 14px;
            background: var(--wa-panel);
            box-shadow: var(--wa-shadow-sm);
            max-width: 100%;
            word-wrap: break-word;
        }

        /* Responder a un mensaje (hilo/cita estilo WhatsApp) */
        .wa-reply-trigger {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 1px solid var(--wa-border);
            background: var(--wa-panel);
            color: var(--wa-text-secondary);
            font-size: 13px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: var(--wa-transition);
            box-shadow: var(--wa-shadow-sm);
        }

        .wa-bubble:hover .wa-reply-trigger {
            opacity: 1;
        }

        .wa-react-trigger {
            position: absolute;
            top: -10px;
            right: 20px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 1px solid var(--wa-border);
            background: var(--wa-panel);
            font-size: 12px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: var(--wa-transition);
            box-shadow: var(--wa-shadow-sm);
        }

        .wa-bubble:hover .wa-react-trigger {
            opacity: 1;
        }

        .wa-delete-trigger {
            position: absolute;
            top: -10px;
            right: 50px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 1px solid var(--wa-border);
            background: var(--wa-panel);
            color: var(--wa-danger);
            font-size: 12px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: var(--wa-transition);
            box-shadow: var(--wa-shadow-sm);
        }

        .wa-bubble:hover .wa-delete-trigger {
            opacity: 1;
        }

        .wa-forward-trigger {
            position: absolute;
            top: -10px;
            right: 80px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 1px solid var(--wa-border);
            background: var(--wa-panel);
            color: var(--wa-text-secondary);
            font-size: 13px;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: var(--wa-transition);
            box-shadow: var(--wa-shadow-sm);
        }

        .wa-bubble:hover .wa-forward-trigger {
            opacity: 1;
        }

        .wa-reaction-picker {
            position: absolute;
            top: -46px;
            right: -10px;
            display: flex;
            gap: 2px;
            background: var(--wa-panel);
            border: 1px solid var(--wa-border);
            border-radius: 999px;
            padding: 4px 6px;
            box-shadow: var(--wa-shadow-sm);
            z-index: 5;
        }

        .wa-reaction-picker-btn {
            border: 0;
            background: transparent;
            font-size: 17px;
            line-height: 1;
            cursor: pointer;
            padding: 3px;
            border-radius: 50%;
            transition: var(--wa-transition);
        }

        .wa-reaction-picker-btn:hover {
            background: var(--wa-bg);
        }

        .wa-reactions {
            display: flex;
            gap: 3px;
            margin-top: 4px;
        }

        .wa-reaction-chip {
            background: var(--wa-panel);
            border: 1px solid var(--wa-border);
            border-radius: 999px;
            padding: 1px 6px;
            font-size: 13px;
            box-shadow: var(--wa-shadow-sm);
        }

        .wa-deleted-notice {
            font-style: italic;
            color: var(--wa-text-tertiary);
            font-size: 13px;
        }

        .wa-message.empleado .wa-deleted-notice,
        .wa-message.ia .wa-deleted-notice {
            color: rgba(255,255,255,0.75);
        }

        .wa-quoted {
            border-left: 3px solid var(--wa-accent);
            background: rgba(0,0,0,0.05);
            border-radius: 6px;
            padding: 6px 8px;
            margin-bottom: 6px;
            font-size: 12.5px;
            overflow: hidden;
        }

        .wa-quoted-author {
            font-weight: 600;
            color: var(--wa-accent);
            margin-bottom: 2px;
        }

        .wa-quoted-text {
            color: var(--wa-text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wa-message.empleado .wa-quoted,
        .wa-message.ia .wa-quoted {
            background: rgba(255,255,255,0.15);
            border-left-color: white;
        }

        .wa-message.empleado .wa-quoted-author,
        .wa-message.ia .wa-quoted-author {
            color: white;
        }

        .wa-message.empleado .wa-quoted-text,
        .wa-message.ia .wa-quoted-text {
            color: rgba(255,255,255,0.85);
        }

        /* Barra "respondiendo a..." arriba del composer */
        .wa-reply-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: var(--wa-space-2);
            background: var(--wa-bg);
            border-left: 3px solid var(--wa-accent);
            border-radius: var(--wa-radius-md);
            padding: 6px 10px;
            margin-bottom: 8px;
        }

        .wa-reply-bar-content {
            min-width: 0;
            flex: 1;
        }

        .wa-reply-bar-author {
            font-weight: 600;
            font-size: 12.5px;
            color: var(--wa-accent);
        }

        .wa-reply-bar-text {
            font-size: 12.5px;
            color: var(--wa-text-secondary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wa-reply-bar-cancel {
            border: 0;
            background: transparent;
            color: var(--wa-text-secondary);
            cursor: pointer;
            font-size: 14px;
            flex-shrink: 0;
        }

        .wa-message.cliente .wa-bubble {
            border-bottom-left-radius: 4px;
        }

        .wa-message.empleado .wa-bubble {
            background: var(--wa-accent);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .wa-message.ia .wa-bubble {
            background: var(--wa-accent);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .wa-message.sistema .wa-bubble {
            background: rgba(0,0,0,0.05);
            color: var(--wa-text-secondary);
            text-align: center;
            font-size: 12px;
            padding: 6px 14px;
        }

        .wa-media {
            max-width: 320px;
            border-radius: var(--wa-radius-md);
            display: block;
            margin-bottom: 6px;
        }

        .wa-message audio {
            width: 280px;
            max-width: 100%;
            height: 44px;
            margin-top: 4px;
        }

        .wa-message-time {
            font-size: 11px;
            color: var(--wa-text-tertiary);
            align-self: flex-end;
            margin-top: 2px;
        }

        .wa-message.empleado .wa-message-time {
            color: rgba(255,255,255,0.7);
        }

        .wa-message.ia .wa-message-time {
            color: rgba(255,255,255,0.7);
        }

        .wa-date-divider {
            align-self: center;
            padding: 4px 12px;
            background: var(--wa-panel);
            border-radius: var(--wa-radius-full);
            color: var(--wa-text-secondary);
            font-size: 12px;
            font-weight: 500;
            box-shadow: var(--wa-shadow-sm);
            margin: 8px 0;
        }

        /* ================================================
           INPUT COMPOSER PREMIUM
           ================================================ */

        .wa-composer {
            border-top: 1px solid var(--wa-border);
            background: var(--wa-panel);
            padding: var(--wa-space-4);
        }

        .wa-compose-row {
            display: flex;
            gap: var(--wa-space-2);
            align-items: flex-end;
        }

        .wa-textarea {
            flex: 1;
            min-height: 44px;
            max-height: 160px;
            border: 1px solid var(--wa-border);
            border-radius: var(--wa-radius-lg);
            padding: 12px 16px;
            color: var(--wa-text);
            background: var(--wa-bg);
            font-size: 14px;
            resize: none;
            font-family: inherit;
            line-height: 1.5;
            transition: var(--wa-transition);
        }

        .wa-textarea:focus {
            outline: none;
            border-color: var(--wa-accent);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: var(--wa-panel);
        }

        .wa-textarea::placeholder {
            color: var(--wa-text-tertiary);
        }

        .wa-send {
            height: 44px;
            min-width: 80px;
            border-radius: var(--wa-radius-lg);
            border: 0;
            background: var(--wa-accent);
            color: white;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: var(--wa-transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .wa-send:hover {
            background: var(--wa-accent-hover);
        }

        .wa-file-input {
            display: none;
        }

        .wa-attach-btn {
            height: 44px;
            width: 44px;
            border-radius: var(--wa-radius-lg);
            border: 1px solid var(--wa-border);
            background: var(--wa-bg);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--wa-text-secondary);
            transition: var(--wa-transition);
            font-size: 18px;
            flex-shrink: 0;
        }

        .wa-attach-btn:hover {
            border-color: var(--wa-border-hover);
            background: var(--wa-panel);
            color: var(--wa-accent);
        }

        .wa-attach-preview {
            display: flex;
            align-items: center;
            gap: var(--wa-space-2);
            margin-top: var(--wa-space-2);
            padding: var(--wa-space-2);
            background: var(--wa-bg);
            border-radius: var(--wa-radius-md);
        }

        /* ================================================
           GRABACIÓN DE AUDIO (estilo WhatsApp)
           ================================================ */

        .wa-mic-btn.recording {
            background: var(--wa-danger);
            border-color: var(--wa-danger);
            color: white;
        }

        .wa-record-bar {
            display: none;
            align-items: center;
            gap: var(--wa-space-2);
            flex: 1;
            height: 44px;
            padding: 0 var(--wa-space-3);
            border-radius: var(--wa-radius-lg);
            background: var(--wa-danger-soft);
            border: 1px solid var(--wa-danger);
            user-select: none;
            touch-action: none;
        }

        .wa-record-bar.active {
            display: flex;
        }

        /* El estado "grabando" se controla con una clase en #wa-compose-row (protegido con
           wire:ignore.self) en vez de estilos inline en cada hijo, porque wire:poll re-renderiza
           el composer cada pocos segundos y borraría cualquier style/class puesto directo en los
           hijos, cancelando la grabación en curso. */
        .wa-compose-row.wa-recording-active > .wa-attach-btn,
        .wa-compose-row.wa-recording-active > .wa-textarea,
        .wa-compose-row.wa-recording-active > .wa-send {
            display: none;
        }

        .wa-record-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--wa-danger);
            flex-shrink: 0;
            animation: wa-record-pulse 1s ease-in-out infinite;
        }

        .wa-record-bar.paused .wa-record-dot {
            animation: none;
            opacity: 0.5;
        }

        @keyframes wa-record-pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        .wa-record-time {
            font-variant-numeric: tabular-nums;
            font-size: 13px;
            font-weight: 600;
            color: var(--wa-danger);
            min-width: 38px;
            flex-shrink: 0;
        }

        .wa-record-wave {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 3px;
            height: 24px;
            overflow: hidden;
        }

        .wa-record-wave span {
            width: 3px;
            border-radius: 2px;
            background: var(--wa-danger);
            opacity: 0.65;
            height: 6px;
            animation: wa-record-bar-bounce 0.9s ease-in-out infinite;
        }

        .wa-record-bar.paused .wa-record-wave span {
            animation-play-state: paused;
            opacity: 0.35;
        }

        .wa-record-wave span:nth-child(2) { animation-delay: 0.1s; }
        .wa-record-wave span:nth-child(3) { animation-delay: 0.2s; }
        .wa-record-wave span:nth-child(4) { animation-delay: 0.3s; }
        .wa-record-wave span:nth-child(5) { animation-delay: 0.4s; }
        .wa-record-wave span:nth-child(6) { animation-delay: 0.3s; }
        .wa-record-wave span:nth-child(7) { animation-delay: 0.2s; }
        .wa-record-wave span:nth-child(8) { animation-delay: 0.1s; }

        @keyframes wa-record-bar-bounce {
            0%, 100% { height: 6px; }
            50% { height: 20px; }
        }

        .wa-record-hint {
            font-size: 12px;
            color: var(--wa-danger);
            white-space: nowrap;
            flex-shrink: 0;
        }

        .wa-record-action {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            font-size: 15px;
            transition: var(--wa-transition);
        }

        .wa-record-cancel {
            background: transparent;
            color: var(--wa-danger);
        }

        .wa-record-cancel:hover {
            background: rgba(239, 68, 68, 0.15);
        }

        .wa-record-pause {
            background: transparent;
            color: var(--wa-text-secondary);
        }

        .wa-record-pause:hover {
            background: var(--wa-bg);
        }

        .wa-record-send {
            background: var(--wa-accent);
            color: white;
        }

        .wa-record-send:hover {
            background: var(--wa-accent-hover);
        }

        .wa-record-error {
            font-size: 12px;
            color: var(--wa-danger);
            display: block;
            margin-bottom: 8px;
        }

        /* ================================================
           PANEL DERECHO FICHA CLIENTE
           ================================================ */

        .wa-client-header {
            padding: var(--wa-space-4);
            background: var(--wa-panel);
            border-bottom: 1px solid var(--wa-border);
            text-align: center;
        }

        .wa-client-avatar {
            width: 44px;
            height: 44px;
            border-radius: var(--wa-radius-full);
            background: var(--wa-accent-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
            color: var(--wa-accent);
            flex-shrink: 0;
        }

        .wa-client-name {
            font-size: 18px;
            font-weight: 600;
            margin: 0 0 4px;
        }

        .wa-client-phone {
            color: var(--wa-text-secondary);
            font-size: 13px;
        }

        .wa-panel-scroll {
            overflow-y: auto;
            min-height: 0;
            flex: 1;
            padding: var(--wa-space-3) var(--wa-space-4);
            display: flex;
            flex-direction: column;
            gap: var(--wa-space-2);
        }

        .wa-card {
            background: var(--wa-panel);
            border-radius: var(--wa-radius-md);
            padding: var(--wa-space-3);
            box-shadow: var(--wa-shadow-sm);
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .wa-card-title {
            font-weight: 600;
            font-size: 13px;
            color: var(--wa-text);
            margin: 0;
        }

        .wa-card-value {
            color: var(--wa-text-secondary);
            font-size: 14px;
        }

        .wa-profile-card {
            padding: 14px;
            border-radius: 18px;
            background: radial-gradient(circle at top left, #ffffff 0%, #eef4ff 45%, #f8fafc 100%);
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.08);
            display: grid;
            gap: 8px;
            text-align: left;
        }

        .wa-profile-top {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .wa-profile-stack {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .wa-profile-title {
            font-size: 15px;
            font-weight: 800;
            color: var(--wa-text);
            line-height: 1.1;
        }

        .wa-profile-subtitle {
            font-size: 12px;
            color: var(--wa-text-secondary);
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* Línea compacta de metadatos (reemplaza el viejo grid de 2 cajas grandes
           "Primer contacto"/"Asignación" -- esos datos se consultan poco, no
           necesitan tanto espacio fijo siempre visible). */
        .wa-profile-meta-line {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 4px 10px;
            font-size: 12px;
            color: var(--wa-text-secondary);
        }

        .wa-profile-meta-line strong {
            color: var(--wa-text);
            font-weight: 700;
        }

        .wa-profile-meta-sep {
            color: var(--wa-text-tertiary);
        }

        /* Secciones colapsables tipo <details> dentro de la ficha del cliente:
           sin marcador nativo, summary reutiliza el look de .wa-card-title con
           un chevron que rota al abrir. Se usan con wire:ignore.self porque el
           root de este componente tiene wire:poll.3s -- sin eso Livewire
           borraría el atributo "open" (estado que solo vive en el DOM) en el
           siguiente re-render automático, igual que ya pasaba con el composer
           de audio (ver wa-compose-row). */
        .wa-collapsible {
            cursor: default;
        }

        .wa-collapsible > summary {
            cursor: pointer;
            list-style: none;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .wa-collapsible > summary::-webkit-details-marker {
            display: none;
        }

        .wa-collapsible > summary::after {
            content: '';
            width: 8px;
            height: 8px;
            flex-shrink: 0;
            border-right: 2px solid var(--wa-text-tertiary);
            border-bottom: 2px solid var(--wa-text-tertiary);
            transform: rotate(45deg);
            transition: transform .18s ease;
        }

        .wa-collapsible[open] > summary::after {
            transform: rotate(-135deg);
        }

        /* .wa-card ya espacia sus hijos con flex+gap; esto es solo para
           collapsibles sueltos (fuera de una .wa-card) como el de clasificación. */
        .wa-collapsible:not(.wa-card) > summary ~ * {
            margin-top: 6px;
        }

        .wa-type-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .wa-type-btn {
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid var(--wa-border);
            background: rgba(255,255,255,0.72);
            color: var(--wa-text-secondary);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--wa-transition);
        }

        .wa-type-btn:hover {
            border-color: var(--wa-accent);
            color: var(--wa-accent);
        }

        .wa-type-btn.active.success { background: var(--wa-success-soft); border-color: #a7f3d0; color: #065f46; }
        .wa-type-btn.active.warning { background: #fef3c7; border-color: #fcd34d; color: #92400e; }
        .wa-type-btn.active.info { background: var(--wa-accent-soft); border-color: #bfdbfe; color: #1d4ed8; }
        .wa-type-btn.active.bot { background: #f3e8ff; border-color: #d8b4fe; color: #7e22ce; }
        .wa-type-btn.active.muted { background: #e2e8f0; border-color: #cbd5e1; color: #334155; }

        /* Etiquetas manuales de conversación (estilo WhatsApp Business) */
        .wa-tag-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            min-height: 20px;
            padding: 2px 8px;
            border-radius: var(--wa-radius-full);
            color: white;
            font-size: 11px;
            font-weight: 600;
            line-height: 1;
            border: 0;
            cursor: pointer;
            transition: var(--wa-transition);
        }

        .wa-tag-add {
            background: transparent;
            border: 1px dashed var(--wa-border);
            color: var(--wa-text-secondary);
        }

        .wa-tag-add:hover {
            border-color: var(--wa-accent);
            color: var(--wa-accent);
        }

        .wa-tag-unassigned {
            background: transparent;
        }

        .wa-tag-filter {
            opacity: 0.55;
        }

        .wa-tag-filter.active {
            opacity: 1;
            outline: 2px solid var(--wa-text);
            outline-offset: 1px;
        }

        .wa-etiqueta-creator {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 8px;
            padding: 10px;
            border: 1px solid var(--wa-border);
            border-radius: var(--wa-radius-md);
            background: var(--wa-bg);
        }

        .wa-etiqueta-input {
            padding: 8px 10px;
            border-radius: var(--wa-radius-sm);
            border: 1px solid var(--wa-border);
            font-size: 13px;
        }

        .wa-etiqueta-palette {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .wa-etiqueta-swatch {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            padding: 0;
        }

        .wa-etiqueta-swatch.active {
            border-color: var(--wa-text);
        }

        .wa-purchase-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            border-bottom: 1px solid var(--wa-border);
        }

        .wa-purchase-item:last-child {
            border-bottom: 0;
        }

        .wa-account-card {
            border: 1px solid var(--wa-border);
            border-radius: var(--wa-radius-md);
            background: linear-gradient(165deg, #ffffff 0%, #f8fbff 100%);
            padding: 9px;
            display: grid;
            gap: 6px;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .wa-account-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
        }

        .wa-account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .wa-account-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--wa-text);
            letter-spacing: .02em;
        }

        .wa-status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid transparent;
        }

        .wa-status-pill.success {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }

        .wa-status-pill.warning {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }

        .wa-status-pill.danger {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .wa-status-pill.muted {
            background: #f8fafc;
            color: #334155;
            border-color: #cbd5e1;
        }

        .wa-account-meta {
            display: grid;
            gap: 5px;
            font-size: 12px;
            color: var(--wa-text-secondary);
        }

        .wa-copy-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 5px;
        }

        .wa-copy-chip {
            border: 1px solid #bfdbfe;
            background: linear-gradient(145deg, #eff6ff 0%, #dbeafe 100%);
            color: #1d4ed8;
            border-radius: 999px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .16s ease, background .16s ease;
        }

        .wa-copy-chip:hover {
            transform: translateY(-1px) scale(1.02);
            box-shadow: 0 6px 14px rgba(29, 78, 216, 0.2);
        }

        .wa-copy-chip.is-copied {
            background: linear-gradient(145deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #86efac;
            color: #166534;
            animation: waCopiedPulse .45s ease;
        }

        @keyframes waCopiedPulse {
            0% { transform: scale(.94); }
            60% { transform: scale(1.04); }
            100% { transform: scale(1); }
        }

        /* Cuentas de proveedor: a diferencia de .wa-account-card (pensada para ~5
           cuentas de un cliente), un proveedor tiene en promedio ~20 cuentas y
           creciendo -- lista compacta con scroll propio + buscador en vez de una
           card grande por cuenta. */
        .wa-provider-accounts-list {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-height: 380px;
            overflow-y: auto;
        }

        .wa-provider-service-heading {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--wa-text-tertiary);
            margin-top: 6px;
        }

        .wa-provider-service-heading:first-child {
            margin-top: 0;
        }

        .wa-provider-account-row {
            border: 1px solid var(--wa-border);
            border-radius: var(--wa-radius-sm);
            padding: 8px;
            display: grid;
            gap: 4px;
        }

        .wa-provider-account-main {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wa-provider-account-id {
            font-family: monospace;
            font-size: 12px;
            font-weight: 600;
            color: var(--wa-text);
        }

        .wa-empty {
            margin: auto;
            text-align: center;
            color: var(--wa-text-tertiary);
            padding: var(--wa-space-6);
        }

        /* Aviso chico no bloqueante cuando un mensaje queda "en cola" por el límite
           anti-spam (ver WhatsAppRateLimiter) -- se autoesconde solo, no bloquea al
           empleado. Visibilidad controlada por JS (Livewire.on('chat-throttled', ...)). */
        .wa-toast {
            position: fixed;
            left: 50%;
            bottom: 24px;
            transform: translateX(-50%);
            z-index: 10000;
            max-width: 90vw;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: var(--wa-radius-md);
            background: var(--wa-text);
            color: var(--wa-panel);
            font-size: 13px;
            font-weight: 600;
            box-shadow: var(--wa-shadow-lg);
        }

        .wa-back {
            display: none;
        }

        /* ================================================
           MODAL CONFIGURACION
           ================================================ */

        .wa-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .wa-modal {
            background: var(--wa-panel);
            border-radius: var(--wa-radius-xl);
            padding: var(--wa-space-5);
            width: 520px;
            max-width: 90%;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: var(--wa-shadow-lg);
            animation: wa-fadeIn 0.18s ease;
        }

        @keyframes wa-fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .wa-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--wa-space-4);
        }

        .wa-modal-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .wa-modal-close {
            width: 36px;
            height: 36px;
            border-radius: var(--wa-radius-md);
            border: 0;
            background: transparent;
            cursor: pointer;
            font-size: 18px;
            color: var(--wa-text-secondary);
            transition: var(--wa-transition);
        }

        .wa-modal-close:hover {
            background: var(--wa-bg);
            color: var(--wa-text);
        }

        .wa-forward-candidate {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border: 0;
            border-radius: var(--wa-radius-md);
            background: transparent;
            cursor: pointer;
            text-align: left;
            transition: var(--wa-transition);
        }

        .wa-forward-candidate:hover {
            background: var(--wa-bg);
        }

        /* ================================================
           AJUSTES EXTRA DARK MODE (ALTO CONTRASTE)
           ================================================ */
        :root[data-dark-mode="true"] .wa-item.active {
            background: #1e2b3f;
            border-left-color: #60a5fa;
        }

        :root[data-dark-mode="true"] .wa-load-older {
            background: #1a2331;
            border-color: #334155;
            color: #d1d9e6;
        }

        :root[data-dark-mode="true"] .wa-load-older:hover {
            border-color: #60a5fa;
            color: #eff6ff;
            background: #223148;
        }

        :root[data-dark-mode="true"] .wa-message.sistema .wa-bubble {
            background: #1e293b;
            color: #d1d9e6;
        }

        :root[data-dark-mode="true"] .wa-date-divider {
            background: #1a2331;
            color: #c8d4e4;
        }

        :root[data-dark-mode="true"] .wa-profile-card {
            background: radial-gradient(circle at top left, #1b2432 0%, #151e2a 48%, #121a24 100%);
            border-color: #2c3a4d;
            box-shadow: 0 16px 34px rgba(0, 0, 0, 0.45);
        }

        :root[data-dark-mode="true"] .wa-type-btn {
            background: #182231;
            border-color: #334155;
            color: #c7d2e2;
        }

        :root[data-dark-mode="true"] .wa-type-btn:hover {
            background: #223248;
            color: #f1f5f9;
        }

        :root[data-dark-mode="true"] .wa-type-btn.active.success { background: #17352e; border-color: #1f725b; color: #8be7c6; }
        :root[data-dark-mode="true"] .wa-type-btn.active.warning { background: #3b2c14; border-color: #6b4f1f; color: #f5d187; }
        :root[data-dark-mode="true"] .wa-type-btn.active.info { background: #1c2f4a; border-color: #274f86; color: #a4c9ff; }
        :root[data-dark-mode="true"] .wa-type-btn.active.bot { background: #332045; border-color: #5c3a7a; color: #dbbcff; }
        :root[data-dark-mode="true"] .wa-type-btn.active.muted { background: #1f2836; border-color: #344154; color: #c5cfdb; }

        :root[data-dark-mode="true"] .wa-account-card {
            background: linear-gradient(165deg, #1a2331 0%, #131c28 100%);
            border-color: #2c3a4d;
        }

        :root[data-dark-mode="true"] .wa-account-card:hover {
            box-shadow: 0 10px 18px rgba(0, 0, 0, 0.4);
        }

        :root[data-dark-mode="true"] .wa-status-pill.success {
            background: #163b31;
            color: #8be7c6;
            border-color: #1f725b;
        }

        :root[data-dark-mode="true"] .wa-status-pill.warning {
            background: #3b2c14;
            color: #f5d187;
            border-color: #6b4f1f;
        }

        :root[data-dark-mode="true"] .wa-status-pill.danger {
            background: #432126;
            color: #ffb3be;
            border-color: #7a3542;
        }

        :root[data-dark-mode="true"] .wa-status-pill.muted {
            background: #1f2836;
            color: #c5cfdb;
            border-color: #344154;
        }

        :root[data-dark-mode="true"] .wa-copy-chip {
            border-color: #3d5c85;
            background: linear-gradient(145deg, #1f3047 0%, #1a2739 100%);
            color: #b9d6ff;
        }

        :root[data-dark-mode="true"] .wa-copy-chip:hover {
            box-shadow: 0 6px 14px rgba(40, 110, 214, 0.28);
        }

        :root[data-dark-mode="true"] .wa-copy-chip.is-copied {
            background: linear-gradient(145deg, #1b3d35 0%, #123329 100%);
            border-color: #2f7c67;
            color: #8be7c6;
        }

        :root[data-dark-mode="true"] .wa-badge.success { background: #17352e; color: #8be7c6; }
        :root[data-dark-mode="true"] .wa-badge.info { background: #1d2e47; color: #a4c9ff; }
        :root[data-dark-mode="true"] .wa-badge.warning { background: #3b2c14; color: #f5d187; }
        :root[data-dark-mode="true"] .wa-badge.muted { background: #1f2836; color: #c5cfdb; }
        :root[data-dark-mode="true"] .wa-badge.bot { background: #322046; color: #dbbcff; }
        :root[data-dark-mode="true"] .wa-badge.soporte      { background: #b91c1c; color: #fecaca; }
        :root[data-dark-mode="true"] .wa-badge.cobrar       { background: #3b2c14; color: #f5d187; }
        :root[data-dark-mode="true"] .wa-badge.quitar       { background: #431407; color: #fed7aa; }
        :root[data-dark-mode="true"] .wa-badge.cuenta-caida { background: #7f1d1d; color: #fecaca; }
        :root[data-dark-mode="true"] .wa-badge.renovar      { background: #1e3a8a; color: #bfdbfe; }
        :root[data-dark-mode="true"] .wa-badge.caida-pro    { background: #451a03; color: #fde68a; }

        :root[data-dark-mode="true"] .wa-operator-chip {
            background: #1f2836;
            color: #d8e2ef;
            border-color: #344154;
        }

        :root[data-dark-mode="true"] .wa-operator-chip::before {
            background: #7f95b3;
        }

        /* ================================================
           MOBILE NAV (oculto en desktop)
           ================================================ */

        .wa-mobile-nav {
            display: none;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-bottom: 1px solid var(--wa-border);
            background: var(--wa-panel);
            flex-shrink: 0;
            min-height: 48px;
        }

        .wa-mobile-nav-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--wa-text);
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .wa-profile-btn-mobile {
            display: none !important;
        }

        .wa-profile-icon-mobile {
            display: none !important;
        }

        /* ================================================
           MENÚ MÓVIL (3 PUNTOS)
           ================================================ */

        .wa-mobile-menu-wrap {
            display: none;
            position: relative;
            flex-shrink: 0;
        }

        .wa-mobile-menu-panel {
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            width: 260px;
            max-height: 72vh;
            overflow-y: auto;
            background: var(--wa-panel);
            border: 1px solid var(--wa-border);
            border-radius: var(--wa-radius-lg);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
            z-index: 200;
        }

        .wa-menu-divider {
            height: 1px;
            background: var(--wa-border);
            margin: 2px 0;
        }

        .wa-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            padding: 11px 16px;
            border: 0;
            background: transparent;
            color: var(--wa-text);
            font-size: 14px;
            cursor: pointer;
            text-align: left;
            gap: 8px;
            transition: var(--wa-transition);
        }

        .wa-menu-item:active {
            background: var(--wa-bg);
        }

        .wa-menu-item.danger {
            color: #b91c1c;
        }

        .wa-menu-info {
            padding: 8px 16px 6px;
            font-size: 12px;
            color: var(--wa-text-secondary);
        }

        .wa-menu-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            padding: 10px 14px;
        }

        .wa-menu-search {
            padding: 8px 12px 10px;
            border-top: 1px solid var(--wa-border);
        }

        .wa-menu-select-row {
            padding: 6px 14px 10px;
        }

        .wa-menu-select-label {
            font-size: 11px;
            color: var(--wa-text-secondary);
            display: block;
            margin-bottom: 4px;
        }

        /* ================================================
           RESPONSIVE
           ================================================ */

        @media (max-width: 1180px) {
            .wa-helpdesk {
                grid-template-columns: 320px 1fr;
            }
            .wa-right {
                display: none;
            }
        }

        @media (max-width: 768px) {
            /* ---- Contenedor principal: cambia de grid a bloque ---- */
            .wa-helpdesk {
                display: block;
                position: relative;
                height: calc(100dvh - 56px);
                overflow: hidden;
            }

            /* ---- Contenedor: altura correcta en móvil ---- */
            .wa-helpdesk {
                max-height: calc(100dvh - 56px);
            }

            /* ---- Los 3 paneles ocupan todo el espacio del contenedor ---- */
            /* Nota: se usa aside.wa-column:not(.wa-right) en lugar de .wa-column:first-child
               porque <style> es el primer hijo del .wa-helpdesk y rompe el selector :first-child */
            aside.wa-column:not(.wa-right),
            .wa-chat,
            .wa-column.wa-right {
                position: absolute;
                inset: 0;
                width: 100%;
                display: flex !important;
                flex-direction: column;
                transition: transform 280ms cubic-bezier(0.4, 0, 0.2, 1);
                overflow: hidden;
                pointer-events: none;
            }

            /* ---- PANEL: lista (.wa-pane-list) ---- */
            .wa-pane-list aside.wa-column:not(.wa-right) { transform: translateX(0);     z-index: 3; pointer-events: auto; }
            .wa-pane-list .wa-chat                        { transform: translateX(100%);  z-index: 2; }
            .wa-pane-list .wa-column.wa-right             { transform: translateX(200%); z-index: 1; }

            /* ---- PANEL: chat (.wa-pane-chat) ---- */
            .wa-pane-chat aside.wa-column:not(.wa-right) { transform: translateX(-100%); z-index: 1; }
            .wa-pane-chat .wa-chat                        { transform: translateX(0);     z-index: 3; pointer-events: auto; }
            .wa-pane-chat .wa-column.wa-right             { transform: translateX(100%);  z-index: 2; }

            /* ---- PANEL: perfil (.wa-pane-profile) ---- */
            .wa-pane-profile aside.wa-column:not(.wa-right) { transform: translateX(-200%); z-index: 1; }
            .wa-pane-profile .wa-chat                        { transform: translateX(-100%); z-index: 2; }
            .wa-pane-profile .wa-column.wa-right             { transform: translateX(0);     z-index: 3; pointer-events: auto; }

            /* ---- Lista: scroll táctil ---- */
            .wa-list {
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-y: contain;
            }

            /* ---- Mensajes: scroll táctil ---- */
            .wa-messages {
                padding: var(--wa-space-3);
                -webkit-overflow-scrolling: touch;
                overscroll-behavior-y: contain;
            }

            /* ---- iOS auto-zoom fix: inputs >= 16px ---- */
            .wa-search,
            .wa-textarea {
                font-size: 16px !important;
            }

            /* ---- Toolbar de la lista ---- */
            .wa-toolbar {
                padding-top: 12px;
                padding-bottom: 8px;
            }

            /* ---- Mensaje máximo ancho ---- */
            .wa-message {
                max-width: 85%;
            }

            /* ---- Botón volver ---- */
            .wa-back {
                display: inline-flex !important;
            }

            /* ---- Nav mobile del panel de perfil ---- */
            .wa-mobile-nav {
                display: flex;
            }

            /* ---- Botón "Ver perfil" en header del chat ---- */
            .wa-profile-btn-mobile {
                display: inline-flex !important;
            }

            /* ---- Ícono perfil en fila del nombre (chat header) ---- */
            .wa-profile-icon-mobile {
                display: inline-flex !important;
                flex-shrink: 0;
            }

            /* ---- Chat info ocupa ancho completo en mobile ---- */
            .wa-chat-info {
                width: 100%;
            }

            /* ---- Ocultar secciones del header que van al menú 3 puntos ---- */
            .wa-chat-actions,
            .wa-chat-header .wa-profile-meta-line,
            .wa-chat-search {
                display: none !important;
            }

            /* ---- Mostrar botón menú 3 puntos ---- */
            .wa-mobile-menu-wrap {
                display: flex;
            }

            /* ---- Header del chat: solo 1 fila compacta ---- */
            .wa-chat-header {
                padding: 8px 12px;
            }

            .wa-chat-title-row {
                flex-direction: row;
                align-items: center;
            }

            /* ---- Acciones del chat: scroll horizontal ---- */
            .wa-chat-actions {
                overflow-x: auto;
                flex-wrap: nowrap;
                scrollbar-width: none;
                padding-bottom: 2px;
            }
            .wa-chat-actions::-webkit-scrollbar { display: none; }

            /* ---- Header del chat: apila title y acciones ---- */
            .wa-chat-title-row {
                flex-direction: column;
                gap: 8px;
            }

            /* ---- Composer: espacio para home bar en iOS ---- */
            .wa-composer {
                padding: 10px 12px;
                padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
            }

            /* ---- Quita el overlay (ya no necesario) ---- */
            .wa-helpdesk::after {
                display: none !important;
            }
        }
    </style>

    <div id="wa-toast" class="wa-toast" wire:ignore.self style="display:none;"></div>

    <aside class="wa-column">
         <div class="wa-toolbar">
             <div class="wa-header-row">
                 <h1 class="wa-title">
                     WhatsApp
                     <span class="wa-channel-badge">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.259.489 1.691.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.943L0 24l6.307-1.655A11.793 11.793 0 0012.05 23.786c6.56 0 11.893-5.335 11.897-11.894-.001-3.161-1.235-6.128-3.473-8.365z"/>
                        </svg>
                        Helpdesk
                    </span>
                 </h1>
                 <div class="wa-header-actions">
                     <button wire:click="$toggle('showSettingsModal')" class="wa-icon-btn" title="Configuracion">⚙️</button>
                 </div>
             </div>

             @if($concentracionActive)
                 <div style="display:flex;align-items:center;gap:6px;padding:6px 10px;background:{{ $concentracionLocked ? '#1e293b' : '#eff6ff' }};border-radius:8px;font-size:12px;font-weight:600;color:{{ $concentracionLocked ? '#93c5fd' : '#1d4ed8' }};">
                     <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 4l6 2.67V11c0 3.8-2.6 7.37-6 8.68-3.4-1.31-6-4.88-6-8.68V7.67L12 5z"/></svg>
                     {{ $concentracionLocked ? 'Modo concentración (bloqueado)' : 'Modo concentración activo' }}
                 </div>
             @endif

             <input wire:model.live.debounce.300ms="search" class="wa-search" type="search" placeholder="Buscar cliente, número o mensaje">

             <div class="wa-filters">
                 @foreach([
                     'todos' => 'Todos',
                     'nuevas' => 'Nuevas',
                     'no_leidas' => 'No leídas',
                     'proveedor' => 'Proveedores',
                     'bot' => 'Bots',
                     'asignadas_mi' => 'Mías',
                     'abiertas' => 'Abiertas',
                     'cerradas' => 'Cerradas',
                 ] as $key => $label)
                     <button wire:click="$set('filter', '{{ $key }}')" class="wa-filter {{ $filter === $key ? 'active' : '' }}">
                         {{ $label }}
                     </button>
                 @endforeach
             </div>

             @if($whatsappChannels->isNotEmpty())
                 <div class="wa-filters wa-channel-filters">
                     @foreach($whatsappChannels as $channel)
                         @php
                             $dotColor = match ($channel->color) {
                                 'verde' => '#10b981',
                                 'azul' => '#2563eb',
                                 'naranja' => '#f97316',
                                 default => '#64748b',
                             };
                         @endphp
                         <button
                             wire:click="setChannelFilter({{ $channel->id }})"
                             class="wa-filter {{ $channelFilter === $channel->id ? 'active' : '' }}"
                             title="Filtrar por {{ $channel->display_name ?: $channel->instance_name }}"
                         >
                             <span style="display:inline-block; width:8px; height:8px; border-radius:9999px; background:{{ $dotColor }}; margin-right:6px;"></span>
                             {{ $channel->display_name ?: $channel->instance_name }}
                         </button>
                     @endforeach
                 </div>
             @endif

             @if($allEtiquetas->isNotEmpty())
                 <div class="wa-filters wa-etiqueta-filters">
                     @foreach($allEtiquetas as $etiqueta)
                         <button
                             wire:click="toggleEtiquetaFiltro({{ $etiqueta->id }})"
                             class="wa-tag-chip wa-tag-filter {{ $etiquetaFilter === $etiqueta->id ? 'active' : '' }}"
                             style="background: {{ $etiqueta->color }};"
                         >
                             {{ $etiqueta->nombre }}
                         </button>
                     @endforeach
                 </div>
             @endif
         </div>

         <div class="wa-list" id="wa-conversations-list">
             @forelse($conversations as $conversation)
                 @php
                     $displayName = $conversation->cliente?->nombrecli
                         ?: $conversation->contactoCanal?->nombre
                         ?: data_get($conversation->contactoCanal?->metadata, 'provider_name')
                         ?: $conversation->contactoCanal?->nombre_canal
                         ?: $conversation->contactoCanal?->telefono_normalizado
                         ?: $conversation->contactoCanal?->canal_user_id
                         ?: 'Contacto';
                     $number = $conversation->contactoCanal?->telefono_normalizado
                         ?: $conversation->contactoCanal?->telefono
                         ?: $conversation->contactoCanal?->canal_user_id
                         ?: $conversation->cliente?->telefonocli
                         ?: 'Sin numero';
                     $lastMessage = $conversation->ultimoMensaje;
                     $matchedMessage = $matchedMessages->get($conversation->idconv);
                     $unread = (int) ($conversation->unread_count ?: $conversation->mensajes_no_leidos);
                     $initial = strtoupper(substr($displayName, 0, 1));
                     $channelColor = data_get($conversation->metadata, 'whatsapp_color')
                         ?? data_get($conversation->contactoCanal?->metadata, 'whatsapp_color')
                         ?? 'otro';
                     $channelLabel = match ($channelColor) {
                         'verde' => 'WA Verde',
                         'azul' => 'WA Azul',
                         default => 'WhatsApp',
                     };
                     $contactIdentity = $this->contactIdentity($conversation);
                     $convIdcli = $conversation->idcli;
                     $convPhone = $conversation->contactoCanal?->telefono_normalizado;
                 @endphp
                 <div class="wa-item-wrapper" wire:key="conversation-{{ $conversation->idconv }}">
                 <button type="button" wire:click="selectConversation({{ $conversation->idconv }})" class="wa-item {{ $activeConversationId === $conversation->idconv ? 'active' : '' }} {{ $conversation->pinned_at ? 'pinned' : '' }}">
                     <div class="wa-item-row">
                         <div class="wa-avatar">{{ $initial }}</div>
                         <div class="wa-conversation-content">
                             <div style="display: flex; justify-content: space-between; width: 100%; align-items: baseline;">
                                 <span class="wa-name">{{ $displayName }}</span>
                                 <span class="wa-time">{{ optional($conversation->last_message_at ?: $conversation->ultima_actividad)->format('H:i') }}</span>
                             </div>
                            <div class="wa-channel-meta">
                                <span class="wa-number">{{ $number }}</span>
                                <span class="wa-channel-label">
                                    <span class="wa-channel-dot {{ $channelColor }}"></span>
                                    {{ $channelLabel }}
                                </span>
                            </div>
                             <div class="wa-preview">
                                 @if($matchedMessage)
                                     🔎 {!! $this->highlightMessageContent($matchedMessage->contenido, $search) !!}
                                 @elseif($lastMessage?->eliminado_at)
                                     🚫 Mensaje eliminado
                                 @else
                                     {{ $lastMessage?->contenido ?: match($lastMessage?->tipo_contenido) {
                                         'imagen' => '📷 Imagen',
                                         'audio' => '🎤 Audio',
                                         'sticker' => '🧩 Sticker',
                                         'documento', 'archivo' => '📄 Documento',
                                         'video' => '🎬 Video',
                                         default => 'Sin mensajes',
                                     } }}
                                 @endif
                             </div>
                         </div>
                     </div>
                     <div class="wa-item-footer">
                         <span class="wa-operator-chip" title="{{ $conversation->operadorAsignado?->nombreemp ?: 'Sin asignar' }}">
                             {{ $conversation->operadorAsignado?->nombreemp ?: 'Sin asignar' }}
                         </span>
                         <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                             <span class="wa-badge {{ $contactIdentity['tone'] }}">{{ $contactIdentity['label'] }}</span>
                             {{-- Labels de cliente --}}
                             @if($convIdcli && isset($conversationLabels['soporte'][$convIdcli]))
                                 <span class="wa-badge soporte">Soporte</span>
                             @endif
                             @if($convIdcli && isset($conversationLabels['cobrar'][$convIdcli]))
                                 <span class="wa-badge cobrar">Cobrar</span>
                             @endif
                             @if($convIdcli && isset($conversationLabels['quitar'][$convIdcli]))
                                 <span class="wa-badge quitar">Quitar</span>
                             @endif
                             @if($convIdcli && isset($conversationLabels['cuenta_caida'][$convIdcli]))
                                 <span class="wa-badge cuenta-caida">Cuenta Caída</span>
                             @endif
                             {{-- Labels de proveedor --}}
                             @if($convPhone && isset($conversationLabels['renovar'][$convPhone]))
                                 <span class="wa-badge renovar">Renovar</span>
                             @endif
                             @if($convPhone && isset($conversationLabels['caida_pro'][$convPhone]))
                                 <span class="wa-badge caida-pro">Caída</span>
                             @endif
                             @foreach($conversation->etiquetas as $etiqueta)
                                 <span class="wa-tag-chip" style="background: {{ $etiqueta->color }};">{{ $etiqueta->nombre }}</span>
                             @endforeach
                             @if($unread > 0)
                                 <span class="wa-badge danger">{{ $unread }}</span>
                             @endif
                         </div>
                     </div>
                 </button>
                 <button
                     type="button"
                     wire:click.stop="togglePin({{ $conversation->idconv }})"
                     class="wa-pin-btn {{ $conversation->pinned_at ? 'active' : '' }}"
                     title="{{ $conversation->pinned_at ? 'Desfijar conversación' : 'Fijar conversación' }}"
                 >📌</button>
                 </div>
             @empty
                 <div class="wa-empty">No hay conversaciones.</div>
             @endforelse
         </div>
     </aside>

    <main class="wa-chat">
        @if($activeConversation)
            @php
                $activeName = $activeConversation->cliente?->nombrecli
                    ?: $activeConversation->contactoCanal?->nombre
                    ?: data_get($activeConversation->contactoCanal?->metadata, 'provider_name')
                    ?: $activeConversation->contactoCanal?->nombre_canal
                    ?: $activeConversation->contactoCanal?->telefono_normalizado
                    ?: 'Contacto';
                $activeNumber = $activeConversation->contactoCanal?->telefono_normalizado
                    ?: $activeConversation->contactoCanal?->telefono
                    ?: $activeConversation->contactoCanal?->canal_user_id
                    ?: $activeConversation->cliente?->telefonocli
                    ?: 'Sin numero';
                $typingOperator = $activeConversation->operadorEscribiendo;
                $isTyping = $typingOperator
                    && $activeConversation->operator_typing_at
                    && $activeConversation->operator_typing_at->gt(now()->subSeconds(8))
                    && $typingOperator->idemp !== auth()->user()?->idemp;
                $activeChannelColor = data_get($activeConversation->metadata, 'whatsapp_color')
                    ?? data_get($activeConversation->contactoCanal?->metadata, 'whatsapp_color')
                    ?? 'otro';
                $activeChannelLabel = match ($activeChannelColor) {
                    'verde' => 'WA Verde',
                    'azul' => 'WA Azul',
                    'naranja' => 'WA Naranja',
                    default => 'WhatsApp',
                };
                $assignedOperatorName = $activeConversation->operadorAsignado?->nombreemp ?? 'Sin asignar';
                $typingLabel = $isTyping ? ($typingOperator->nombreemp.' escribiendo...') : 'Sin actividad de escritura';
                $clientInitial = strtoupper(substr($activeName, 0, 1));
            @endphp

            <header class="wa-chat-header" x-data="{ waSearchOpen: {{ trim($activeMessageSearch) !== '' ? 'true' : 'false' }} }">
                <div class="wa-chat-title-row">
                    <div class="wa-chat-info">
                        <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
                            <button wire:click="backToList" class="wa-icon-btn wa-back" type="button" title="Volver a chats">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                            </button>
                            <div style="flex: 1; min-width: 0;">
                                <h2 class="wa-chat-title">{{ $activeName }}</h2>
                                <div class="wa-chat-subtitle wa-channel-meta">
                                    <span>{{ $activeNumber }}</span>
                                    <span class="wa-channel-label">
                                        <span class="wa-channel-dot {{ $activeChannelColor }}"></span>
                                        {{ $activeChannelLabel }}
                                    </span>
                                </div>
                            </div>
                            <button wire:click="$set('mobilePane', 'profile')" class="wa-icon-btn wa-profile-icon-mobile" type="button" title="Ver ficha del contacto">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </button>
                            {{-- Menú 3 puntos (solo móvil) --}}
                            <div class="wa-mobile-menu-wrap" x-data="{ waMenuOpen: false }">
                                <button @click="waMenuOpen = !waMenuOpen" class="wa-icon-btn" type="button" title="Más opciones">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="5" r="1.2" fill="currentColor" stroke="none"/>
                                        <circle cx="12" cy="12" r="1.2" fill="currentColor" stroke="none"/>
                                        <circle cx="12" cy="19" r="1.2" fill="currentColor" stroke="none"/>
                                    </svg>
                                </button>
                                <div x-show="waMenuOpen" @click.outside="waMenuOpen = false" class="wa-mobile-menu-panel" style="display:none;">
                                    {{-- Badges de tipo y estado --}}
                                    <div class="wa-menu-badges">
                                        <span class="wa-badge {{ $activeContactIdentity['tone'] }}">{{ $activeContactIdentity['label'] }}</span>
                                        <span class="wa-badge info">{{ $activeConversation->estado }}</span>
                                    </div>
                                    <div class="wa-menu-divider"></div>
                                    {{-- Acciones --}}
                                    <button wire:click="takeConversation" @click="waMenuOpen = false" class="wa-menu-item" type="button">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                        Tomar conversación
                                    </button>
                                    @if(in_array($activeConversation->estado, ['cerrado', 'cerrada'], true))
                                        <button wire:click="reopenConversation" @click="waMenuOpen = false" class="wa-menu-item" type="button">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                            Reabrir
                                        </button>
                                    @else
                                        <button wire:click="closeConversation" @click="waMenuOpen = false" class="wa-menu-item" type="button">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                                            Cerrar conversación
                                        </button>
                                    @endif
                                    <button
                                        wire:click="clearActiveConversationHistory"
                                        wire:confirm="¿Seguro que deseas borrar solo el historial del chat seleccionado? Esta acción no se puede deshacer."
                                        @click="waMenuOpen = false"
                                        class="wa-menu-item danger"
                                        type="button"
                                    >
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                        Limpiar historial
                                    </button>
                                    @can('chat.supervisor')
                                        <div class="wa-menu-divider"></div>
                                        <div class="wa-menu-select-row">
                                            <span class="wa-menu-select-label">Asignar operador</span>
                                            <select wire:change="assignTo($event.target.value)" class="wa-select" style="width: 100%; font-size: 14px;">
                                                <option value="">Seleccionar…</option>
                                                @foreach($operators as $operator)
                                                    <option value="{{ $operator->idemp }}">{{ $operator->nombreemp }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endcan
                                    <div class="wa-menu-divider"></div>
                                    <div class="wa-menu-info">Operador: <strong>{{ $assignedOperatorName }}</strong></div>
                                    <div class="wa-menu-search">
                                        <input wire:model.live.debounce.300ms="activeMessageSearch" class="wa-search" type="search" placeholder="Buscar en conversación" style="font-size:16px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wa-chat-actions">
                        <button wire:click="$set('mobilePane', 'profile')" class="wa-action wa-profile-btn-mobile" type="button" title="Ver ficha del cliente">
                            👤 Ver perfil
                        </button>
                        <button
                            type="button"
                            class="wa-icon-btn"
                            title="Buscar en esta conversación"
                            @click="waSearchOpen = !waSearchOpen; if (waSearchOpen) $nextTick(() => $refs.waChatSearchInput.focus())"
                        >🔍</button>
                        <span class="wa-badge {{ $activeContactIdentity['tone'] }}">{{ $activeContactIdentity['label'] }}</span>
                        <span class="wa-badge info">{{ $activeConversation->estado }}</span>
                        <button wire:click="takeConversation" class="wa-action" type="button">Tomar</button>
                        @can('chat.supervisor')
                            <select wire:change="assignTo($event.target.value)" class="wa-select">
                                <option value="">Asignar</option>
                                @foreach($operators as $operator)
                                    <option value="{{ $operator->idemp }}">{{ $operator->nombreemp }}</option>
                                @endforeach
                            </select>
                        @endcan
                        @if(in_array($activeConversation->estado, ['cerrado', 'cerrada'], true))
                            <button wire:click="reopenConversation" class="wa-action primary" type="button">Reabrir</button>
                        @else
                            <button wire:click="closeConversation" class="wa-action" type="button">Cerrar</button>
                        @endif
                        <button
                            wire:click="clearActiveConversationHistory"
                            wire:confirm="¿Seguro que deseas borrar solo el historial del chat seleccionado? Esta acción no se puede deshacer."
                            class="wa-action"
                            type="button"
                            style="border-color: #fca5a5; color: #b91c1c;"
                        >
                            Limpiar chat
                        </button>
                    </div>
                </div>

                <div class="wa-profile-meta-line" style="margin-top: 8px;">
                    <span>Operador: <strong>{{ $assignedOperatorName }}</strong></span>
                    <span class="wa-profile-meta-sep">·</span>
                    <span>{{ $messagesLoaded }} mensajes</span>
                    @if($isTyping)
                        <span class="wa-profile-meta-sep">·</span>
                        <span class="wa-badge success">{{ $typingLabel }}</span>
                    @endif
                </div>

                <div class="wa-chat-search" x-show="waSearchOpen" style="display:none;">
                    <input x-ref="waChatSearchInput" wire:model.live.debounce.300ms="activeMessageSearch" class="wa-search" type="search" placeholder="Buscar dentro de esta conversación">
                    <span class="wa-chat-search-meta">{{ trim($activeMessageSearch) !== '' ? 'Filtro activo' : 'Sin filtro' }}</span>
                </div>
            </header>

            <section class="wa-messages" id="wa-messages">
                @if($messagesHasMore)
                    <button class="wa-load-older" type="button" wire:click="loadOlderMessages">Cargar mensajes anteriores</button>
                @endif
                <div class="wa-messages-meta">Mostrando últimos {{ $messagesLoaded }} mensajes</div>
                @php $lastDate = null; @endphp
                @foreach($messages as $message)
                    @php
                        $dateKey = optional($message->created_at)->format('Y-m-d');
                        $type = $message->tipo ?: $message->tipo_contenido;
                        $mediaUrl = $message->media_playable_url;
                        $quotedPreview = $message->replyTo ? \Illuminate\Support\Str::limit($message->replyTo->quoted_preview, 80) : null;
                    @endphp
                    @if($dateKey !== $lastDate)
                        <div class="wa-date-divider">{{ optional($message->created_at)->format('d/m/Y') }}</div>
                        @php $lastDate = $dateKey; @endphp
                    @endif
                    <article class="wa-message {{ $message->tipo_remitente }}">
                        <div class="wa-bubble">
                            @if(!$message->eliminado_at)
                                <button type="button" class="wa-reply-trigger" title="Responder" wire:click="startReply({{ $message->idmsg }})">↩</button>
                                <button type="button" class="wa-react-trigger" title="Reaccionar" wire:click="toggleReactionPicker({{ $message->idmsg }})">😀</button>
                                <button type="button" class="wa-forward-trigger" title="Reenviar" wire:click="startForward({{ $message->idmsg }})">↪</button>
                                @if($message->tipo_remitente === 'empleado')
                                    <button
                                        type="button"
                                        class="wa-delete-trigger"
                                        title="Borrar mensaje"
                                        wire:click="deleteMessage({{ $message->idmsg }})"
                                        wire:confirm="¿Borrar este mensaje? Se intentará borrar también en WhatsApp, pero eso solo funciona si no pasó mucho tiempo desde que se envió."
                                    >🗑</button>
                                @endif

                                @if($reactionPickerForIdmsg === $message->idmsg)
                                    <div class="wa-reaction-picker">
                                        @foreach(\App\Livewire\Chat\WhatsAppHelpdesk::EMOJIS_REACCION as $emojiOpcion)
                                            <button type="button" class="wa-reaction-picker-btn" wire:click="reactToMessage({{ $message->idmsg }}, '{{ $emojiOpcion }}')">{{ $emojiOpcion }}</button>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            @if($message->eliminado_at)
                                <div class="wa-deleted-notice">🚫 Mensaje eliminado</div>
                            @else
                                @if($message->replyTo)
                                    <div class="wa-quoted">
                                        <div class="wa-quoted-author">{{ $message->replyTo->nombre_remitente }}</div>
                                        <div class="wa-quoted-text">{{ $quotedPreview }}</div>
                                    </div>
                                @endif
                                @if($type === 'imagen' && $mediaUrl)
                                    <img src="{{ $mediaUrl }}" class="wa-media" alt="Imagen recibida">
                                @elseif($type === 'sticker' && $mediaUrl)
                                    <img src="{{ $mediaUrl }}" class="wa-media" style="max-width: 180px;" alt="Sticker recibido">
                                @elseif($type === 'audio' && $mediaUrl)
                                    <audio controls preload="metadata">
                                        <source src="{{ $mediaUrl }}" type="{{ str_contains((string) $message->mime_type, 'audio/') ? strtok((string) $message->mime_type, ';') : 'audio/ogg' }}">
                                        Tu navegador no soporta este audio.
                                    </audio>
                                @elseif($type === 'video' && $mediaUrl)
                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" style="color: inherit;">🎬 Abrir video</a>
                                @elseif(in_array($type, ['documento', 'archivo'], true) && $mediaUrl)
                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" style="color: inherit;">📄 Abrir documento</a>
                                @endif

                                @if($message->contenido !== '')
                                    <div>{!! $this->highlightMessageContent($message->contenido) !!}</div>
                                @endif

                                @if($message->reacciones->isNotEmpty())
                                    <div class="wa-reactions">
                                        @foreach($message->reacciones as $reaccion)
                                            <span class="wa-reaction-chip" title="{{ $reaccion->autor_tipo === 'empleado' ? 'Tu reacción' : 'Reacción del cliente' }}">{{ $reaccion->emoji }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="wa-message-time">
                            {{ optional($message->created_at)->format('H:i') }}
                            @if(in_array($message->tipo_remitente, ['empleado', 'ia'], true))
                                · {{ $message->throttled_until?->isFuture() ? '⏳ En cola' : ($message->error_message ? '⚠️ Error' : ($message->delivered_at ? '✓✓ Enviado' : '✓ Pendiente')) }}
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <footer class="wa-composer">
                @error('messageText') <span style="color: var(--wa-danger); font-size: 12px; display: block; margin-bottom: 8px;">{{ $message }}</span> @enderror
                @error('imageUpload') <span style="color: var(--wa-danger); font-size: 12px; display: block; margin-bottom: 8px;">{{ $message }}</span> @enderror
                @error('audioUpload') <span style="color: var(--wa-danger); font-size: 12px; display: block; margin-bottom: 8px;">{{ $message }}</span> @enderror
                <span class="wa-record-error" id="wa-record-error" style="display: none;"></span>

                @if($replyingToIdmsg)
                    @php $replyingToMessage = $messages->firstWhere('idmsg', $replyingToIdmsg); @endphp
                    @if($replyingToMessage)
                        <div class="wa-reply-bar">
                            <div class="wa-reply-bar-content">
                                <div class="wa-reply-bar-author">Respondiendo a {{ $replyingToMessage->nombre_remitente }}</div>
                                <div class="wa-reply-bar-text">{{ \Illuminate\Support\Str::limit($replyingToMessage->quoted_preview, 100) }}</div>
                            </div>
                            <button type="button" class="wa-reply-bar-cancel" title="Cancelar respuesta" wire:click="cancelReply">✕</button>
                        </div>
                    @endif
                @endif

                @if($quickResponseSuggestions->isNotEmpty() && str_starts_with(trim((string) $messageText), '/'))
                    <div class="wa-quick-suggestions" style="margin-bottom: 8px; border: 1px solid var(--wa-border); border-radius: 10px; background: #fff; max-height: 220px; overflow-y: auto;">
                        @foreach($quickResponseSuggestions as $quick)
                            <button
                                type="button"
                                data-quick-response-id="{{ $quick->id }}"
                                data-quick-response-content="{{ e($quick->contenido) }}"
                                style="display: block; width: 100%; text-align: left; border: 0; border-bottom: 1px solid var(--wa-border); background: transparent; padding: 10px 12px; cursor: pointer;"
                            >
                                <div style="font-weight: 600; color: var(--wa-text);">/{{ $quick->comando }} · {{ $quick->titulo }}</div>
                                <div style="font-size: 12px; color: var(--wa-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ \Illuminate\Support\Str::limit($quick->contenido, 110) }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="wa-compose-row" id="wa-compose-row" wire:ignore.self>
                    @if($settings['chat_allow_image'])
                        <label class="wa-attach-btn" id="wa-image-attach-btn" title="Adjuntar imagen (o pega con Ctrl+V)">
                            📷
                            <input wire:model="imageUpload" class="wa-file-input" type="file" accept="image/*">
                        </label>
                    @endif

                    @if($settings['chat_allow_audio'])
                        <button type="button" id="wa-mic-btn" class="wa-attach-btn wa-mic-btn" title="Grabar nota de voz">
                            🎤
                        </button>
                    @endif

                    @if($settings['chat_allow_text'])
                        <textarea wire:model.live="messageText" wire:keydown="markTyping" id="wa-composer-textarea" class="wa-textarea" placeholder="Escribe un mensaje... (pega una imagen con Ctrl+V)" rows="1"></textarea>
                        <button wire:click="sendText" class="wa-send" type="button" data-chat-send>Enviar</button>
                    @endif

                    @if($settings['chat_allow_audio'])
                        <div class="wa-record-bar" id="wa-record-bar" wire:ignore>
                            <button type="button" class="wa-record-action wa-record-cancel" id="wa-record-cancel" title="Cancelar">✕</button>
                            <span class="wa-record-dot"></span>
                            <span class="wa-record-time" id="wa-record-time">0:00</span>
                            <div class="wa-record-wave">
                                <span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>
                            </div>
                            <span class="wa-record-hint" id="wa-record-hint">◀ Desliza para cancelar</span>
                            <button type="button" class="wa-record-action wa-record-pause" id="wa-record-pause" title="Pausar">⏸</button>
                            <button type="button" class="wa-record-action wa-record-send" id="wa-record-send" title="Enviar nota de voz">➤</button>
                        </div>
                    @endif
                </div>

                @if($imageUpload || $audioUpload)
                    <div class="wa-attach-preview">
                        @if($imageUpload)
                            <span>📷 {{ $imageUpload->getClientOriginalName() }}</span>
                            <button wire:click="sendImage" class="wa-action primary" type="button">Enviar imagen</button>
                        @endif
                        @if($audioUpload)
                            <span>🎤 {{ $audioUpload->getClientOriginalName() }}</span>
                            <button wire:click="sendAudio" class="wa-action primary" type="button">Enviar audio</button>
                        @endif
                    </div>
                @endif
            </footer>
        @else
            <div class="wa-empty">
                <div style="font-size: 48px; margin-bottom: 16px;">💬</div>
                <strong style="font-size: 16px; display: block; margin-bottom: 4px;">Selecciona una conversación</strong>
                <span style="color: var(--wa-text-tertiary);">Elige un chat de la lista para empezar a atender</span>
                <button type="button" wire:click="$set('mobilePane', 'list')" class="wa-action wa-profile-btn-mobile" style="margin-top: 16px;">
                    ← Ver conversaciones
                </button>
            </div>
        @endif
    </main>

     <aside class="wa-column wa-right">
         <!-- Nav mobile: volver al chat -->
         <div class="wa-mobile-nav">
             <button type="button" wire:click="$set('mobilePane', 'chat')" class="wa-icon-btn" style="flex-shrink:0;">
                 <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
             </button>
             <span class="wa-mobile-nav-title">
                 {{ $activeConversation ? ($activeConversation->cliente?->nombrecli ?: $activeName ?? 'Ficha del cliente') : 'Ficha del cliente' }}
             </span>
         </div>
         @if($activeConversation)
             @php
                 $client = $activeConversation->cliente;
                 $firstContact = data_get($activeConversation->metadata, 'primer_contacto_at') ?: optional($activeConversation->created_at)->toIso8601String();
                 $contactTypeOptions = [
                     'cliente' => ['label' => 'Cliente', 'tone' => 'success'],
                     'proveedor' => ['label' => 'Proveedor', 'tone' => 'warning'],
                     'grupo' => ['label' => 'Grupo', 'tone' => 'info'],
                     'bot' => ['label' => 'Bot', 'tone' => 'bot'],
                     'desconocido' => ['label' => 'No clasificado', 'tone' => 'muted'],
                 ];
             @endphp
             <div class="wa-client-header wa-profile-card">
                 <div class="wa-profile-top">
                     <div class="wa-client-avatar">{{ $clientInitial }}</div>
                     <div class="wa-profile-stack">
                         <div class="wa-profile-title">{{ $client?->nombrecli ?: $activeName }}</div>
                         <div class="wa-profile-subtitle">
                             <span>{{ $client?->telefonocli ?: $activeNumber }}</span>
                             <span class="wa-badge {{ $activeContactIdentity['tone'] }}">{{ $activeContactIdentity['label'] }}</span>
                             <span class="wa-badge outline">{{ $activeChannelLabel }}</span>
                         </div>
                     </div>
                 </div>
                 <details class="wa-collapsible" wire:ignore.self>
                     <summary class="wa-card-title">Cambiar clasificación</summary>
                     <div class="wa-type-actions">
                         @foreach($contactTypeOptions as $typeKey => $typeOption)
                             <button
                                 type="button"
                                 wire:click="setContactType('{{ $typeKey }}')"
                                 class="wa-type-btn {{ $activeContactIdentity['type'] === $typeKey ? 'active '.$typeOption['tone'] : '' }}"
                             >
                                 {{ $typeOption['label'] }}
                             </button>
                         @endforeach
                     </div>
                 </details>
                 <div>
                     <div class="wa-card-title" style="margin-bottom: 8px;">Etiquetas</div>
                     <div class="wa-type-actions">
                         @foreach($activeConversation->etiquetas as $etiqueta)
                             <button
                                 type="button"
                                 wire:click="toggleEtiquetaEnConversacion({{ $etiqueta->id }})"
                                 class="wa-tag-chip wa-tag-assigned"
                                 style="background: {{ $etiqueta->color }};"
                                 title="Quitar etiqueta"
                             >
                                 {{ $etiqueta->nombre }} ✕
                             </button>
                         @endforeach
                         <button type="button" wire:click="toggleEtiquetaCreator" class="wa-tag-chip wa-tag-add">
                             + Etiqueta
                         </button>
                     </div>

                     @if($showEtiquetaCreator)
                         <div class="wa-etiqueta-creator">
                             <input
                                 type="text"
                                 wire:model="newEtiquetaNombre"
                                 maxlength="30"
                                 placeholder="Nombre de la etiqueta"
                                 class="wa-etiqueta-input"
                             >
                             <div class="wa-etiqueta-palette">
                                 @foreach(\App\Models\ChatEtiqueta::PALETA as $colorOpcion)
                                     <button
                                         type="button"
                                         wire:click="$set('newEtiquetaColor', '{{ $colorOpcion }}')"
                                         class="wa-etiqueta-swatch {{ $newEtiquetaColor === $colorOpcion ? 'active' : '' }}"
                                         style="background: {{ $colorOpcion }};"
                                     ></button>
                                 @endforeach
                             </div>
                             <div style="display: flex; gap: 8px;">
                                 <button type="button" wire:click="guardarNuevaEtiqueta" class="wa-action primary">Crear</button>
                                 <button type="button" wire:click="toggleEtiquetaCreator" class="wa-action">Cancelar</button>
                             </div>
                         </div>
                     @endif

                     @php
                         $etiquetasAsignadasIds = $activeConversation->etiquetas->pluck('id');
                         $etiquetasDisponibles = $allEtiquetas->reject(fn ($e) => $etiquetasAsignadasIds->contains($e->id));
                     @endphp
                     @if($etiquetasDisponibles->isNotEmpty())
                         <div class="wa-type-actions" style="margin-top: 6px;">
                             @foreach($etiquetasDisponibles as $etiqueta)
                                 <button
                                     type="button"
                                     wire:click="toggleEtiquetaEnConversacion({{ $etiqueta->id }})"
                                     class="wa-tag-chip wa-tag-unassigned"
                                     style="border: 1px solid {{ $etiqueta->color }}; color: {{ $etiqueta->color }};"
                                     title="Asignar etiqueta"
                                 >
                                     {{ $etiqueta->nombre }}
                                 </button>
                             @endforeach
                         </div>
                     @endif
                 </div>
             </div>

             <div class="wa-panel-scroll">
                 <details class="wa-card wa-collapsible" wire:ignore.self>
                     <summary class="wa-card-title">Información</summary>
                     <div class="wa-card-value">Origen: {{ $activeConversation->origen ?: 'WhatsApp' }}</div>
                     <div style="font-size: 12px; color: var(--wa-text-secondary);">ID canal: {{ $activeConversation->contactoCanal?->canal_user_id ?: 'Sin dato' }}</div>
                     <div class="wa-profile-meta-line" style="margin-top: 4px;">
                         <span>Primer contacto: <strong>{{ \Carbon\Carbon::parse($firstContact)->format('d/m/Y H:i') }}</strong></span>
                         <span class="wa-profile-meta-sep">·</span>
                         <span>Asignado a: <strong>{{ $assignedOperatorName }}</strong></span>
                     </div>
                 </details>

                 @if($activeContactIdentity['type'] !== 'proveedor')
                 <details class="wa-card wa-collapsible" wire:ignore.self open>
                     <summary class="wa-card-title">Usuarios activos del cliente ({{ $clientActiveUsers->count() }})</summary>

                     @forelse($clientActiveUsers as $activeUser)
                         <div class="wa-account-card">
                             <div class="wa-account-header">
                                 <div class="wa-account-title">{{ $activeUser['service_name'] ?: $activeUser['service_code'] }}</div>
                                 <span class="wa-status-pill {{ $activeUser['status']['tone'] }}">{{ $activeUser['status']['label'] }}</span>
                             </div>

                             <div class="wa-account-meta">
                                 @if($activeUser['service_code'] === 'SPOTIFY')
                                     <div>Spotify</div>
                                     <div class="wa-copy-row">
                                         <span>Cuenta Admin: {{ $activeUser['account_user'] ?: '-' }} | Clave: {{ $activeUser['account_pass'] ?: '-' }}</span>
                                         @if($activeUser['account_user'])
                                             <button type="button" class="wa-copy-chip" data-copy-value="{{ $activeUser['account_user'] }}" data-copy-label-default="Copiar admin" data-copy-label-copied="Admin copiado">Copiar admin</button>
                                         @endif
                                         @if($activeUser['account_pass'])
                                             <button type="button" class="wa-copy-chip" data-copy-value="{{ $activeUser['account_pass'] }}" data-copy-label-default="Copiar clave admin" data-copy-label-copied="Clave copiada">Copiar clave admin</button>
                                         @endif
                                     </div>
                                     <div class="wa-copy-row">
                                         <span>Cuenta de usuario: {{ $activeUser['spotify_user'] ?: '-' }} | Clave: {{ $activeUser['spotify_pass'] ?: '-' }}</span>
                                         @if($activeUser['spotify_user'])
                                             <button type="button" class="wa-copy-chip" data-copy-value="{{ $activeUser['spotify_user'] }}" data-copy-label-default="Copiar usuario" data-copy-label-copied="Usuario copiado">Copiar usuario</button>
                                         @endif
                                         @if($activeUser['spotify_pass'])
                                             <button type="button" class="wa-copy-chip" data-copy-value="{{ $activeUser['spotify_pass'] }}" data-copy-label-default="Copiar clave" data-copy-label-copied="Clave copiada">Copiar clave</button>
                                         @endif
                                     </div>
                                 @else
                                     <div>Servicio: {{ $activeUser['service_name'] ?: $activeUser['service_code'] }}</div>
                                     <div>Cuenta: {{ $activeUser['account_id'] ?: '-' }}</div>
                                     <div class="wa-copy-row">
                                         <span>Usuario: {{ $activeUser['account_user'] ?: '-' }}</span>
                                         @if($activeUser['account_user'])
                                             <button type="button" class="wa-copy-chip" data-copy-value="{{ $activeUser['account_user'] }}" data-copy-label-default="Copiar usuario" data-copy-label-copied="Usuario copiado">Copiar usuario</button>
                                         @endif
                                     </div>
                                     <div class="wa-copy-row">
                                         <span>Contrasena: {{ $activeUser['account_pass'] ?: '-' }}</span>
                                         @if($activeUser['account_pass'])
                                             <button type="button" class="wa-copy-chip" data-copy-value="{{ $activeUser['account_pass'] }}" data-copy-label-default="Copiar clave" data-copy-label-copied="Clave copiada">Copiar clave</button>
                                         @endif
                                     </div>
                                     @if(!in_array($activeUser['service_code'], ['MAGIS', 'FLUJO'], true))
                                         <div>Perfil: {{ $activeUser['profile'] ?: '-' }}</div>
                                         <div>PIN de perfil: {{ $activeUser['profile_pin'] ?: '-' }}</div>
                                     @endif
                                 @endif

                                 <div>Vence: {{ $activeUser['expires_at'] ? \Carbon\Carbon::parse($activeUser['expires_at'])->format('d/m/Y') : '-' }}</div>
                             </div>
                         </div>
                     @empty
                         <span style="color: var(--wa-text-secondary); font-size: 13px;">No tiene usuarios activos registrados.</span>
                     @endforelse
                 </details>
                 @else
                 <details class="wa-card wa-collapsible" wire:ignore.self open>
                     <summary class="wa-card-title">Cuentas de este proveedor ({{ $providerAccounts['total'] ?? 0 }})</summary>

                     @if(! ($providerAccounts['proveedor'] ?? null))
                         <span style="color: var(--wa-text-secondary); font-size: 13px;">
                             No se encontró un proveedor con este número. Podés revisarlo en
                             <a href="{{ route('proveedores') }}" target="_blank">Proveedores ↗</a>.
                         </span>
                     @else
                         <input type="search" wire:model.live.debounce.300ms="providerAccountSearch" class="wa-search" placeholder="Buscar por servicio, usuario o ID...">

                         <div class="wa-provider-accounts-list">
                             @forelse($providerAccounts['groups'] as $serviceCode => $cuentasDelServicio)
                                 <div class="wa-provider-service-heading">{{ $cuentasDelServicio->first()['service_name'] }} ({{ $cuentasDelServicio->count() }})</div>
                                 @foreach($cuentasDelServicio as $item)
                                     <div class="wa-provider-account-row" wire:key="provider-account-{{ $item['idcue'] }}">
                                         <div class="wa-provider-account-main">
                                             <span class="wa-status-pill {{ $item['estado']['tone'] }}">{{ $item['estado']['label'] }}</span>
                                             <span class="wa-provider-account-id">{{ $item['idcue'] }}</span>
                                             <span style="margin-left:auto; font-size:11px; color:var(--wa-text-tertiary);">Vence {{ $item['vencimiento'] ? \Carbon\Carbon::parse($item['vencimiento'])->format('d/m/Y') : '-' }}</span>
                                         </div>
                                         <div class="wa-copy-row">
                                             <span>Usuario: {{ $item['usuario'] ?: '-' }}</span>
                                             @if($item['usuario'])<button type="button" class="wa-copy-chip" data-copy-value="{{ $item['usuario'] }}" data-copy-label-default="Copiar usuario" data-copy-label-copied="Copiado">Copiar</button>@endif
                                         </div>
                                         <div class="wa-copy-row">
                                             <span>Clave: {{ $item['contrasena'] ?: '-' }}</span>
                                             @if($item['contrasena'])<button type="button" class="wa-copy-chip" data-copy-value="{{ $item['contrasena'] }}" data-copy-label-default="Copiar clave" data-copy-label-copied="Copiado">Copiar</button>@endif
                                         </div>
                                     </div>
                                 @endforeach
                             @empty
                                 <span style="color: var(--wa-text-secondary); font-size: 13px;">Sin cuentas activas de este proveedor.</span>
                             @endforelse
                         </div>

                         <a href="{{ route('cuentas') }}" target="_blank" class="wa-action" style="text-align:center;">Ver todas en Cuentas ↗</a>
                     @endif
                 </details>
                 @endif

                 @if($soporteNotice)
                 <section class="wa-card" style="border-left: 3px solid var(--wa-success); background: var(--wa-success-soft);">
                     <div style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:#065f46;">
                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                         {{ $soporteNotice }}
                     </div>
                 </section>
                 @endif

                 @if($clientePendingSoporte)
                 <section class="wa-card" style="border-left: 3px solid var(--wa-danger); background: var(--wa-danger-soft);">
                     <div class="wa-card-title" style="display:flex; align-items:center; gap:8px;">
                         <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:var(--wa-danger);flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                         Soporte pendiente
                         <span class="wa-badge soporte" style="margin-left:auto;">#{{ $clientePendingSoporte->idsop }}</span>
                     </div>

                     <div style="display:flex; flex-direction:column; gap:8px; font-size:13px; padding-top:2px;">
                         <div>
                             <div style="font-size:11px; color:var(--wa-text-secondary); text-transform:uppercase; letter-spacing:0.04em;">Tipo</div>
                             <div style="font-weight:600; text-transform:capitalize; margin-top:2px;">{{ $clientePendingSoporte->tipo }}</div>
                         </div>

                         @if($clientePendingSoporte->cuenta?->valor?->servicio)
                         <div>
                             <div style="font-size:11px; color:var(--wa-text-secondary); text-transform:uppercase; letter-spacing:0.04em;">Servicio</div>
                             <div style="margin-top:2px;">{{ $clientePendingSoporte->cuenta->valor->servicio->nombreser }}</div>
                         </div>
                         @endif

                         <div>
                             <div style="font-size:11px; color:var(--wa-text-secondary); text-transform:uppercase; letter-spacing:0.04em;">Descripción del cliente</div>
                             <div style="margin-top:2px; color:var(--wa-text-secondary); white-space:pre-wrap;">{{ $clientePendingSoporte->descripcion }}</div>
                         </div>
                     </div>

                     <div style="border-top:1px solid var(--wa-border); padding-top:10px; margin-top:4px; display:flex; flex-direction:column; gap:6px;">
                         <div style="font-size:12px; font-weight:600; color:var(--wa-text);">Anotar solución</div>

                         @error('soporteSolucion')
                             <div style="font-size:12px; color:var(--wa-danger);">{{ $message }}</div>
                         @enderror

                         <textarea
                             wire:model="soporteSolucion"
                             placeholder="Describe cómo se resolvió el problema..."
                             rows="3"
                             style="width:100%; resize:vertical; min-height:68px; font-size:13px; padding:8px 10px; border:1px solid var(--wa-border); border-radius:var(--wa-radius-sm); background:var(--wa-panel); color:var(--wa-text); font-family:inherit; line-height:1.5;"
                         ></textarea>

                         <button
                             type="button"
                             wire:click="atenderSoporteDesdeChat({{ $clientePendingSoporte->idsop }})"
                             wire:loading.attr="disabled"
                             wire:target="atenderSoporteDesdeChat"
                             style="width:100%; padding:9px 12px; background:var(--wa-success); color:white; border:none; border-radius:var(--wa-radius-sm); font-size:13px; font-weight:600; cursor:pointer; transition:opacity var(--wa-transition);"
                             onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'"
                         >
                             <span wire:loading.remove wire:target="atenderSoporteDesdeChat">✓ Marcar como atendido</span>
                             <span wire:loading wire:target="atenderSoporteDesdeChat">Guardando...</span>
                         </button>
                     </div>

                     <div style="font-size:11px; color:var(--wa-text-tertiary);">
                         Creado {{ $clientePendingSoporte->created_at?->diffForHumans() }}
                     </div>
                 </section>
                 @endif

                 <details class="wa-card wa-collapsible" wire:ignore.self>
                     <summary class="wa-card-title">Historial de compras</summary>
                     @forelse($client?->ventas?->take(6) ?? [] as $sale)
                         <div class="wa-purchase-item">
                             <span>{{ optional($sale->fechaven)->format('d/m/Y') ?: $sale->idven }}</span>
                             <span style="font-weight: 600;">${{ number_format((float) $sale->totalpagoven, 2) }}</span>
                         </div>
                     @empty
                         <span style="color: var(--wa-text-secondary); font-size: 13px;">Sin compras registradas.</span>
                     @endforelse
                 </details>

                 <details class="wa-card wa-collapsible" wire:ignore.self>
                     <summary class="wa-card-title">Notas internas</summary>
                     <span style="color: var(--wa-text-secondary); font-size: 13px;">{{ data_get($activeConversation->metadata, 'notas') ?: 'Sin notas.' }}</span>
                 </details>
             </div>
         @else
             <div class="wa-empty">
                 <div style="font-size: 48px; margin-bottom: 16px;">👤</div>
                 <span style="color: var(--wa-text-tertiary);">Selecciona una conversación para ver la ficha del cliente</span>
             </div>
         @endif
     </aside>

     <!-- Modal Reenviar mensaje -->
     @if($forwardingIdmsg)
     <div class="wa-modal-overlay" wire:click.self="cancelForward">
         <div class="wa-modal" style="max-width: 420px;">
             <div class="wa-modal-header">
                 <h2 class="wa-modal-title">↪ Reenviar mensaje</h2>
                 <button wire:click="cancelForward" class="wa-modal-close">×</button>
             </div>

             <input wire:model.live.debounce.300ms="forwardSearch" class="wa-search" type="search" placeholder="Buscar conversación..." style="margin-bottom: 12px;">

             <div style="display: flex; flex-direction: column; gap: 4px; max-height: 360px; overflow-y: auto;">
                 @forelse($this->forwardCandidates() as $candidate)
                     @php
                         $candidateName = $candidate->cliente?->nombrecli
                             ?: $candidate->contactoCanal?->nombre
                             ?: $candidate->contactoCanal?->nombre_canal
                             ?: $candidate->contactoCanal?->telefono_normalizado
                             ?: 'Contacto';
                         $candidateNumber = $candidate->contactoCanal?->telefono_normalizado
                             ?: $candidate->contactoCanal?->canal_user_id
                             ?: $candidate->cliente?->telefonocli
                             ?: '';
                     @endphp
                     <button type="button" wire:key="forward-candidate-{{ $candidate->idconv }}" wire:click="forwardTo({{ $candidate->idconv }})" class="wa-forward-candidate">
                         <span class="wa-avatar" style="width: 28px; height: 28px; font-size: 11px; flex-shrink: 0;">{{ strtoupper(substr($candidateName, 0, 1)) }}</span>
                         <span style="display: flex; flex-direction: column; align-items: flex-start; min-width: 0;">
                             <span style="font-weight: 600; font-size: 13px;">{{ $candidateName }}</span>
                             <span style="font-size: 12px; color: var(--wa-text-secondary);">{{ $candidateNumber }}</span>
                         </span>
                     </button>
                 @empty
                     <div style="padding: 16px; text-align: center; color: var(--wa-text-secondary); font-size: 13px;">Sin resultados.</div>
                 @endforelse
             </div>
         </div>
     </div>
     @endif

     <!-- Modal Configuracion -->
     @if($showSettingsModal)
     <div class="wa-modal-overlay" wire:click.self="$toggle('showSettingsModal')">
         <div class="wa-modal">
             <div class="wa-modal-header">
                 <h2 class="wa-modal-title">⚙️ Configuración Chat WhatsApp</h2>
                 <button wire:click="$toggle('showSettingsModal')" class="wa-modal-close">×</button>
             </div>

             <div style="display: grid; gap: 16px;">
                 @if($settingsNotice)
                     <div class="wa-card" style="border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534;">
                         <div style="font-size: 13px; font-weight: 600;">{{ $settingsNotice }}</div>
                     </div>
                 @endif

                 <div class="wa-card">
                     <div class="wa-card-title">CHAT_WEBHOOK_TOKEN</div>
                     <input type="text" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.chat_webhook_token" wire:change="saveSetting('chat_webhook_token', $event.target.value)" placeholder="test_token_123 (desarrollo)">
                     <div style="font-size: 11px; color: var(--wa-text-tertiary); margin-top: 4px;">
                         Desarrollo: <code>test_token_123</code>
                     </div>
                 </div>

                 <div class="wa-card">
                     <div class="wa-card-title">N8N_WEBHOOK_URL</div>
                     <input type="text" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.n8n_webhook_url" wire:change="saveSetting('n8n_webhook_url', $event.target.value)" placeholder="http://localhost:5678/webhook/chat-outbound">
                     <div style="font-size: 11px; color: var(--wa-text-tertiary); margin-top: 4px;">
                         Desarrollo: <code>http://localhost:5678/webhook/chat-outbound</code>
                     </div>
                 </div>

                 <div class="wa-card">
                     <div class="wa-card-title">Evolution API URL</div>
                     <input type="text" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.evoapi_base_url" wire:change="saveSetting('evoapi_base_url', $event.target.value)" placeholder="https://evoapi.abigailsoft.com">
                     <div style="font-size: 11px; color: var(--wa-text-tertiary); margin-top: 4px;">
                         Desarrollo: <code>http://localhost:8081</code>
                     </div>
                 </div>

                 <div class="wa-card">
                     <div class="wa-card-title">Evolution API Key</div>
                     <input type="password" class="wa-search" style="margin-top: 8px;" wire:model.blur="settings.evoapi_api_key" wire:change="saveSetting('evoapi_api_key', $event.target.value)" placeholder="API Key de Evolution">
                 </div>

                 <hr style="border: 0; border-top: 1px solid var(--wa-border); margin: 4px 0;">

                 <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Instancias WhatsApp</h3>
                 <div class="wa-card">
                     <div style="display: grid; gap: 10px;">
                         <div style="display: grid; gap: 8px; grid-template-columns: 1fr 1fr;">
                             <input type="text" class="wa-search" wire:model.defer="channelInstanceName" placeholder="instance_name (ej. bot-pagos)">
                             <input type="text" class="wa-search" wire:model.defer="channelDisplayName" placeholder="Nombre visible">
                         </div>

                         <div style="display: grid; gap: 8px; grid-template-columns: 1fr 1fr;">
                             <input type="password" class="wa-search" wire:model.defer="channelApiKey" placeholder="API Key de la instancia">
                             <input type="text" class="wa-search" wire:model.defer="channelServerUrl" placeholder="https://evoapi.abigailsoft.com">
                         </div>

                         <div style="display: grid; gap: 8px; grid-template-columns: 1fr 1fr 1fr; align-items: center;">
                             <select class="wa-search" wire:model.defer="channelColor">
                                 <option value="verde">Verde</option>
                                 <option value="azul">Azul</option>
                                 <option value="naranja">Naranja</option>
                                 <option value="otro">Otro</option>
                             </select>
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--wa-text-secondary);">
                                 <input type="checkbox" wire:model.defer="channelIsActive" style="width: 16px; height: 16px;"> Activa
                             </label>
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--wa-text-secondary);">
                                 <input type="checkbox" wire:model.defer="channelOutboundEnabled" style="width: 16px; height: 16px;"> Salida habilitada
                             </label>
                         </div>

                         @error('channelInstanceName')<span style="color: var(--wa-danger); font-size: 12px;">{{ $message }}</span>@enderror
                         @error('channelApiKey')<span style="color: var(--wa-danger); font-size: 12px;">{{ $message }}</span>@enderror
                         @error('channelServerUrl')<span style="color: var(--wa-danger); font-size: 12px;">{{ $message }}</span>@enderror

                         <div style="display: flex; gap: 8px;">
                             <button wire:click="saveChannel" class="wa-send" style="flex: 1;">
                                 {{ $editingChannelId ? 'Actualizar instancia' : 'Guardar instancia' }}
                             </button>
                             <button wire:click="resetChannelForm" class="wa-action" style="flex: 1;">Nueva</button>
                         </div>
                     </div>

                     <div style="margin-top: 14px; display: grid; gap: 8px; max-height: 220px; overflow-y: auto;">
                         @forelse($whatsappChannels as $channel)
                             <div style="display: grid; gap: 6px; padding: 10px; border: 1px solid var(--wa-border); border-radius: 8px;">
                                 <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                     <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                                         <span style="width: 10px; height: 10px; border-radius: 9999px; display: inline-block; background: {{ $channel->color === 'verde' ? '#10b981' : ($channel->color === 'azul' ? '#2563eb' : ($channel->color === 'naranja' ? '#f97316' : '#64748b')) }};"></span>
                                         <strong style="font-size: 13px;">{{ $channel->display_name ?: $channel->instance_name }}</strong>
                                     </div>
                                     <div style="display: flex; gap: 6px;">
                                         <button wire:click="editChannel({{ $channel->id }})" class="wa-action" style="padding: 6px 8px;">Editar</button>
                                         <button wire:click="deleteChannel({{ $channel->id }})" class="wa-action" style="padding: 6px 8px; border-color: #fecaca; color: #dc2626;">Eliminar</button>
                                     </div>
                                 </div>
                                 <div style="font-size: 12px; color: var(--wa-text-secondary);">
                                     <div><strong>Instancia:</strong> {{ $channel->instance_name }}</div>
                                     <div><strong>Estado:</strong> {{ $channel->is_active ? 'Activa' : 'Inactiva' }} | {{ $channel->outbound_enabled ? 'Salida ON' : 'Salida OFF' }}</div>
                                 </div>
                             </div>
                         @empty
                             <div style="font-size: 13px; color: var(--wa-text-secondary);">No hay instancias registradas todavía.</div>
                         @endforelse
                     </div>
                 </div>

                 <hr style="border: 0; border-top: 1px solid var(--wa-border); margin: 4px 0;">

                 <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Tipos de mensaje permitidos:</h3>
                 <div class="wa-card">
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Permitir texto</span>
                         <input type="checkbox" wire:change="saveSetting('chat_allow_text', $event.target.checked)" {{ $settings['chat_allow_text'] ? 'checked' : '' }} style="width: 18px; height: 18px;">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Permitir imagen</span>
                         <input type="checkbox" wire:change="saveSetting('chat_allow_image', $event.target.checked)" {{ $settings['chat_allow_image'] ? 'checked' : '' }} style="width: 18px; height: 18px;">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Permitir audio</span>
                         <input type="checkbox" wire:change="saveSetting('chat_allow_audio', $event.target.checked)" {{ $settings['chat_allow_audio'] ? 'checked' : '' }} style="width: 18px; height: 18px;">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center;">
                         <span>Limite upload (MB)</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_max_upload_mb" wire:change="saveSetting('chat_max_upload_mb', $event.target.value)" min="1" max="100">
                     </div>
                 </div>

                 <hr style="border: 0; border-top: 1px solid var(--wa-border); margin: 4px 0;">

                 <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Límite anti-spam de envíos</h3>
                 <div class="wa-card">
                     <div style="font-size: 12px; color: var(--wa-text-secondary); margin-bottom: 4px;">
                         Si se supera cualquiera de estos límites, el siguiente mensaje queda "En cola" unos segundos en vez de mandarse al toque, para evitar que WhatsApp marque la cuenta por actividad de spam/automatización.
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Máx. mensajes en ráfaga corta</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_outbound_burst_limit" wire:change="saveSetting('chat_outbound_burst_limit', $event.target.value)" min="1" max="100">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Ventana de ráfaga (segundos)</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_outbound_burst_window_seconds" wire:change="saveSetting('chat_outbound_burst_window_seconds', $event.target.value)" min="1" max="600">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Máx. mensajes sostenidos</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_outbound_rate_limit" wire:change="saveSetting('chat_outbound_rate_limit', $event.target.value)" min="1" max="1000">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Ventana sostenida (segundos)</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_outbound_rate_window_seconds" wire:change="saveSetting('chat_outbound_rate_window_seconds', $event.target.value)" min="1" max="3600">
                     </div>
                     <div style="font-size: 12px; color: var(--wa-text-secondary); margin: 8px 0 4px;">
                         Espaciado real entre cada envío (aunque no se supere lo de arriba), para que salgan de a uno en vez de casi juntos.
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Espera mínima entre envíos (segundos)</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_outbound_min_gap_seconds" wire:change="saveSetting('chat_outbound_min_gap_seconds', $event.target.value)" min="0" max="120">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                         <span>Espera máxima entre envíos (segundos)</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_outbound_max_gap_seconds" wire:change="saveSetting('chat_outbound_max_gap_seconds', $event.target.value)" min="0" max="300">
                     </div>
                     <div style="display: flex; justify-content: space-between; align-items: center;">
                         <span>Espera máx. en botones de Cuentas/Usuarios (segundos)</span>
                         <input type="number" class="wa-search" style="width: 100px;" wire:model.blur="settings.chat_outbound_admin_max_wait_seconds" wire:change="saveSetting('chat_outbound_admin_max_wait_seconds', $event.target.value)" min="1" max="60">
                     </div>
                 </div>

                 <hr style="border: 0; border-top: 1px solid var(--wa-border); margin: 4px 0;">

                 <h3 style="margin: 0; font-size: 16px; font-weight: 600;">Respuestas rápidas</h3>
                 <div class="wa-card">
                     <div style="display: grid; gap: 10px;">
                         <div style="display: grid; gap: 8px; grid-template-columns: 1fr 1fr;">
                             <input type="text" class="wa-search" wire:model.defer="quickResponseCommand" placeholder="comando (ej: saludo)">
                             <input type="text" class="wa-search" wire:model.defer="quickResponseTitle" placeholder="Título visible">
                         </div>

                         <textarea class="wa-textarea" wire:model.defer="quickResponseContent" rows="3" placeholder="Contenido de la respuesta rápida"></textarea>

                         <div style="display: grid; gap: 8px; grid-template-columns: 120px 1fr; align-items: center;">
                             <input type="number" class="wa-search" wire:model.defer="quickResponseOrder" min="0" max="9999" placeholder="Orden">
                             <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; color: var(--wa-text-secondary);">
                                 <input type="checkbox" wire:model.defer="quickResponseActive" style="width: 16px; height: 16px;"> Activa
                             </label>
                         </div>

                         @error('quickResponseCommand')<span style="color: var(--wa-danger); font-size: 12px;">{{ $message }}</span>@enderror
                         @error('quickResponseTitle')<span style="color: var(--wa-danger); font-size: 12px;">{{ $message }}</span>@enderror
                         @error('quickResponseContent')<span style="color: var(--wa-danger); font-size: 12px;">{{ $message }}</span>@enderror

                         <div style="display: flex; gap: 8px;">
                             <button wire:click="saveQuickResponse" class="wa-send" style="flex: 1;">
                                 {{ $editingQuickResponseId ? 'Actualizar respuesta' : 'Guardar respuesta' }}
                             </button>
                             <button wire:click="resetQuickResponseForm" class="wa-action" style="flex: 1;">Nueva</button>
                         </div>
                     </div>

                     <div style="margin-top: 14px; display: grid; gap: 8px; max-height: 220px; overflow-y: auto;">
                         @forelse($quickResponses as $quick)
                             <div style="display: grid; gap: 6px; padding: 10px; border: 1px solid var(--wa-border); border-radius: 8px; background: {{ $quick->activo ? '#fff' : '#f8fafc' }};">
                                 <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                     <div style="min-width: 0;">
                                         <strong style="font-size: 13px;">/{{ $quick->comando }}</strong>
                                         <span style="font-size: 12px; color: var(--wa-text-secondary);"> · {{ $quick->titulo }}</span>
                                     </div>
                                     <div style="display: flex; gap: 6px;">
                                         <button wire:click="editQuickResponse({{ $quick->id }})" class="wa-action" style="padding: 6px 8px;">Editar</button>
                                         <button wire:click="deleteQuickResponse({{ $quick->id }})" class="wa-action" style="padding: 6px 8px; border-color: #fecaca; color: #dc2626;">Eliminar</button>
                                     </div>
                                 </div>
                                 <div style="font-size: 12px; color: var(--wa-text-secondary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                     {{ $quick->contenido }}
                                 </div>
                             </div>
                         @empty
                             <div style="font-size: 13px; color: var(--wa-text-secondary);">No hay respuestas rápidas registradas.</div>
                         @endforelse
                     </div>
                 </div>

                 <hr style="border: 0; border-top: 1px solid var(--wa-border); margin: 4px 0;">

                 <div class="wa-card">
                     <div class="wa-card-title">Endpoints listos:</div>
                     <div style="display: grid; gap: 12px; margin-top: 8px;">
                         <div>
                             <span style="font-size: 12px; color: var(--wa-text-secondary);">Webhook Inbound:</span>
                             <code style="background: var(--wa-bg); padding: 6px 8px; border-radius: 6px; font-size: 12px; display: block; margin-top: 4px; word-break: break-all;">{{ route('api.chat.whatsapp.inbound') }}</code>
                         </div>
                         <div>
                             <span style="font-size: 12px; color: var(--wa-text-secondary);">Webhook Token Header:</span>
                             <code style="background: var(--wa-bg); padding: 6px 8px; border-radius: 6px; font-size: 12px; display: block; margin-top: 4px;">X-Chat-Webhook-Token</code>
                         </div>
                     </div>
                 </div>

                 <div class="wa-card" style="border: 1px solid #fecaca; background: #fef2f2;">
                     <div class="wa-card-title" style="color: #b91c1c;">Zona de Riesgo</div>
                     <div style="font-size: 12px; color: #7f1d1d; line-height: 1.5;">
                         Esto borra solo historial interno de Streamify (conversaciones, mensajes, contactos y memoria).
                         No elimina mensajes ni historial en WhatsApp/Evolution API.
                     </div>
                     <button
                         wire:click="clearInternalChatHistory"
                         wire:confirm="¿Seguro que deseas borrar todo el historial interno de chats? Esta acción no se puede deshacer."
                         class="wa-action"
                         style="margin-top: 10px; border-color: #fca5a5; color: #b91c1c;"
                     >
                         🗑️ Borrar historial interno
                     </button>
                 </div>

                 <div style="margin-top: 8px;">
                     <button wire:click="$toggle('showSettingsModal')" class="wa-send" style="width: 100%;">Cerrar</button>
                 </div>
             </div>
         </div>
     </div>
     @endif

    <script>
        document.addEventListener('livewire:init', () => {
            const applySuggestionToComposer = (button) => {
                if (!(button instanceof HTMLButtonElement)) {
                    return;
                }

                const content = button.getAttribute('data-quick-response-content') || '';
                const textarea = document.querySelector('.wa-textarea');

                if (!(textarea instanceof HTMLTextAreaElement)) {
                    return;
                }

                textarea.value = content;
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                textarea.dispatchEvent(new Event('change', { bubbles: true }));
                textarea.focus();

                const end = textarea.value.length;
                textarea.setSelectionRange(end, end);
            };

            Livewire.on('chat-scroll-bottom', () => {
                setTimeout(() => {
                    const container = document.getElementById('wa-messages');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }, 100);
            });

            Livewire.on('chat-clear-composer', () => {
                const textarea = document.querySelector('.wa-textarea');
                if (textarea) {
                    textarea.value = '';
                }
            });

            Livewire.on('chat-focus-composer', () => {
                const textarea = document.querySelector('.wa-textarea');
                if (textarea instanceof HTMLTextAreaElement) {
                    textarea.focus();
                    const end = textarea.value.length;
                    textarea.setSelectionRange(end, end);
                }
            });

            function showRecordError(message) {
                const box = document.getElementById('wa-record-error');
                if (!(box instanceof HTMLElement)) return;
                box.textContent = message;
                box.style.display = 'block';
                setTimeout(() => { box.style.display = 'none'; }, 4000);
            }

            const bindComposerMediaControls = () => {
                const composerRow = document.getElementById('wa-compose-row');
                if (!(composerRow instanceof HTMLElement) || composerRow.dataset.mediaBound === 'true') {
                    return;
                }
                composerRow.dataset.mediaBound = 'true';

                // --- Pegar imagen desde el portapapeles ---
                const textarea = document.getElementById('wa-composer-textarea');
                if (textarea instanceof HTMLTextAreaElement) {
                    textarea.addEventListener('paste', (event) => {
                        const items = event.clipboardData ? event.clipboardData.items : null;
                        if (!items) return;

                        for (const item of items) {
                            if (item.type && item.type.startsWith('image/')) {
                                const blob = item.getAsFile();
                                if (!blob) continue;
                                event.preventDefault();

                                const ext = (item.type.split('/')[1] || 'png').split('+')[0];
                                const file = new File([blob], `pegado-${Date.now()}.${ext}`, { type: item.type });

                                @this.upload('imageUpload', file, () => {}, (error) => {
                                    showRecordError(typeof error === 'string' ? error : 'No se pudo pegar la imagen.');
                                });

                                break;
                            }
                        }
                    });
                }

                // --- Grabación de nota de voz (estilo WhatsApp) ---
                const micBtn = document.getElementById('wa-mic-btn');
                const recordBar = document.getElementById('wa-record-bar');

                if (!(micBtn instanceof HTMLElement) || !(recordBar instanceof HTMLElement)) {
                    return;
                }

                const recordTime = document.getElementById('wa-record-time');
                const recordHint = document.getElementById('wa-record-hint');
                const cancelBtn = document.getElementById('wa-record-cancel');
                const pauseBtn = document.getElementById('wa-record-pause');
                const sendBtn = document.getElementById('wa-record-send');

                // Grabamos PCM crudo con Web Audio API y lo empaquetamos como WAV en vez de
                // usar MediaRecorder: Chrome/Edge solo graban a WebM/Opus, que WhatsApp no
                // reproduce (los audios nativos de WhatsApp son OGG/Opus con ptt:true), y este
                // hosting no tiene ffmpeg ni exec para convertir el archivo en el servidor.
                // WAV es más pesado pero lo reproduce cualquier cliente de WhatsApp sin problema.
                let mediaStream = null;
                let audioContext = null;
                let sourceNode = null;
                let processorNode = null;
                let silentGain = null;
                let pcmChunks = [];
                let recordingSampleRate = 44100;
                let startedAt = 0;
                let pausedElapsed = 0;
                let timerInterval = null;
                let dragStartX = null;
                let cancelled = false;
                let isPaused = false;
                let isRecording = false;
                const CANCEL_THRESHOLD = 80;

                const formatTime = (totalSeconds) => {
                    const m = Math.floor(totalSeconds / 60);
                    const s = Math.floor(totalSeconds % 60);
                    return `${m}:${String(s).padStart(2, '0')}`;
                };

                const setComposerVisible = (visible) => {
                    // Clase en el padre (protegido con wire:ignore.self) en vez de estilos
                    // inline en cada hijo, para que sobreviva a los re-renders de wire:poll.
                    composerRow.classList.toggle('wa-recording-active', !visible);
                    recordBar.classList.toggle('active', !visible);
                };

                const encodeWav = (chunks, sampleRate) => {
                    let totalLength = 0;
                    chunks.forEach((chunk) => { totalLength += chunk.length; });

                    const pcm16 = new Int16Array(totalLength);
                    let offset = 0;
                    chunks.forEach((chunk) => {
                        for (let i = 0; i < chunk.length; i++) {
                            const s = Math.max(-1, Math.min(1, chunk[i]));
                            pcm16[offset++] = s < 0 ? s * 0x8000 : s * 0x7fff;
                        }
                    });

                    const blockAlign = 2;
                    const byteRate = sampleRate * blockAlign;
                    const dataSize = pcm16.length * 2;
                    const buffer = new ArrayBuffer(44 + dataSize);
                    const view = new DataView(buffer);

                    const writeString = (pos, str) => {
                        for (let i = 0; i < str.length; i++) {
                            view.setUint8(pos + i, str.charCodeAt(i));
                        }
                    };

                    writeString(0, 'RIFF');
                    view.setUint32(4, 36 + dataSize, true);
                    writeString(8, 'WAVE');
                    writeString(12, 'fmt ');
                    view.setUint32(16, 16, true);
                    view.setUint16(20, 1, true);
                    view.setUint16(22, 1, true);
                    view.setUint32(24, sampleRate, true);
                    view.setUint32(28, byteRate, true);
                    view.setUint16(32, blockAlign, true);
                    view.setUint16(34, 16, true);
                    writeString(36, 'data');
                    view.setUint32(40, dataSize, true);

                    let pcmOffset = 44;
                    for (let i = 0; i < pcm16.length; i++, pcmOffset += 2) {
                        view.setInt16(pcmOffset, pcm16[i], true);
                    }

                    return new Blob([buffer], { type: 'audio/wav' });
                };

                const stopStream = () => {
                    if (processorNode) {
                        processorNode.disconnect();
                        processorNode.onaudioprocess = null;
                        processorNode = null;
                    }
                    if (sourceNode) {
                        sourceNode.disconnect();
                        sourceNode = null;
                    }
                    if (silentGain) {
                        silentGain.disconnect();
                        silentGain = null;
                    }
                    if (audioContext) {
                        audioContext.close().catch(() => {});
                        audioContext = null;
                    }
                    if (mediaStream) {
                        mediaStream.getTracks().forEach((track) => track.stop());
                        mediaStream = null;
                    }
                    if (timerInterval) {
                        clearInterval(timerInterval);
                        timerInterval = null;
                    }
                    isRecording = false;
                };

                const resetRecordBar = () => {
                    stopStream();
                    pcmChunks = [];
                    recordBar.classList.remove('paused');
                    recordBar.style.transform = '';
                    if (recordTime) recordTime.textContent = '0:00';
                    if (recordHint) recordHint.style.display = '';
                    if (pauseBtn) pauseBtn.textContent = '⏸';
                    setComposerVisible(true);
                };

                const startRecording = async () => {
                    let stream;
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    } catch (error) {
                        showRecordError('No se pudo acceder al micrófono. Revisa los permisos del navegador.');
                        return;
                    }

                    mediaStream = stream;
                    pcmChunks = [];
                    cancelled = false;
                    isPaused = false;
                    pausedElapsed = 0;
                    startedAt = Date.now();

                    try {
                        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                        audioContext = new AudioContextClass();
                        recordingSampleRate = audioContext.sampleRate;
                        sourceNode = audioContext.createMediaStreamSource(stream);
                        processorNode = audioContext.createScriptProcessor(4096, 1, 1);
                        processorNode.onaudioprocess = (event) => {
                            if (isPaused || cancelled) return;
                            pcmChunks.push(new Float32Array(event.inputBuffer.getChannelData(0)));
                        };
                        // El processor solo dispara onaudioprocess si está conectado al destino;
                        // usamos una ganancia en 0 para no escuchar el propio micrófono (feedback).
                        silentGain = audioContext.createGain();
                        silentGain.gain.value = 0;
                        sourceNode.connect(processorNode);
                        processorNode.connect(silentGain);
                        silentGain.connect(audioContext.destination);
                    } catch (error) {
                        showRecordError('Tu navegador no soporta grabación de audio.');
                        stopStream();
                        return;
                    }

                    isRecording = true;
                    setComposerVisible(false);
                    if (recordTime) recordTime.textContent = '0:00';
                    timerInterval = setInterval(() => {
                        const elapsed = pausedElapsed + (Date.now() - startedAt) / 1000;
                        if (recordTime) recordTime.textContent = formatTime(elapsed);
                    }, 250);
                };

                const finishRecording = () => {
                    const wasCancelled = cancelled;
                    const recordedChunks = pcmChunks;
                    const sampleRate = recordingSampleRate;
                    resetRecordBar();

                    if (wasCancelled || recordedChunks.length === 0) {
                        return;
                    }

                    const blob = encodeWav(recordedChunks, sampleRate);
                    const file = new File([blob], `nota-voz-${Date.now()}.wav`, { type: 'audio/wav' });

                    @this.upload('audioUpload', file, () => {
                        @this.call('sendAudio');
                    }, (error) => {
                        showRecordError(typeof error === 'string' ? error : 'No se pudo enviar la nota de voz.');
                    });
                };

                const stopAndSend = () => {
                    if (!isRecording) return;
                    cancelled = false;
                    finishRecording();
                };

                const cancelRecording = () => {
                    if (!isRecording) {
                        resetRecordBar();
                        return;
                    }
                    cancelled = true;
                    finishRecording();
                };

                const togglePause = () => {
                    if (!isRecording) return;
                    if (!isPaused) {
                        isPaused = true;
                        pausedElapsed += (Date.now() - startedAt) / 1000;
                        recordBar.classList.add('paused');
                        if (pauseBtn) pauseBtn.textContent = '▶';
                    } else {
                        isPaused = false;
                        startedAt = Date.now();
                        recordBar.classList.remove('paused');
                        if (pauseBtn) pauseBtn.textContent = '⏸';
                    }
                };

                micBtn.addEventListener('click', () => {
                    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || !AudioContextClass) {
                        showRecordError('Tu navegador no soporta grabación de audio.');
                        return;
                    }
                    startRecording();
                });

                if (cancelBtn) cancelBtn.addEventListener('click', cancelRecording);
                if (sendBtn) sendBtn.addEventListener('click', stopAndSend);
                if (pauseBtn) pauseBtn.addEventListener('click', togglePause);

                // Deslizar para cancelar (gesto estilo WhatsApp)
                recordBar.addEventListener('pointerdown', (event) => {
                    if (event.target === cancelBtn || event.target === pauseBtn || event.target === sendBtn) return;
                    dragStartX = event.clientX;
                });

                recordBar.addEventListener('pointermove', (event) => {
                    if (dragStartX === null) return;
                    const delta = event.clientX - dragStartX;
                    if (delta < 0) {
                        recordBar.style.transform = `translateX(${Math.max(delta, -CANCEL_THRESHOLD * 1.5)}px)`;
                        if (recordHint) recordHint.style.display = delta < -20 ? 'none' : '';
                    }
                    if (delta <= -CANCEL_THRESHOLD) {
                        dragStartX = null;
                        cancelRecording();
                    }
                });

                const endDrag = () => {
                    dragStartX = null;
                    recordBar.style.transform = '';
                };

                recordBar.addEventListener('pointerup', endDrag);
                recordBar.addEventListener('pointerleave', endDrag);
            };

            bindComposerMediaControls();

            Livewire.hook('morphed', () => {
                bindComposerMediaControls();
            });

            Livewire.on('chat-notification-sound', () => {
                try {
                    const AudioContext = window.AudioContext || window.webkitAudioContext;
                    if (!AudioContext) return;
                    const context = new AudioContext();
                    const oscillator = context.createOscillator();
                    const gain = context.createGain();

                    oscillator.type = 'sine';
                    oscillator.frequency.value = 840;
                    gain.gain.value = 0.04;

                    oscillator.connect(gain);
                    gain.connect(context.destination);

                    oscillator.start();
                    setTimeout(() => {
                        oscillator.stop();
                        context.close();
                    }, 150);
                } catch (e) {
                    // Ignorar si el navegador bloquea audio sin interacción previa.
                }
            });

            let waToastTimer = null;
            Livewire.on('chat-throttled', ({ seconds }) => {
                const toast = document.getElementById('wa-toast');
                if (!toast) return;

                toast.textContent = `⏳ Mensaje en cola para evitar que WhatsApp restrinja la cuenta. Se envía en ~${seconds}s.`;
                toast.style.display = 'flex';

                clearTimeout(waToastTimer);
                waToastTimer = setTimeout(() => {
                    toast.style.display = 'none';
                }, 4000);
            });

            const bindConversationInfiniteScroll = () => {
                const list = document.getElementById('wa-conversations-list');

                if (!(list instanceof HTMLElement) || list.dataset.infiniteBound === 'true') {
                    return;
                }

                list.dataset.infiniteBound = 'true';

                let isLoading = false;

                list.addEventListener('scroll', () => {
                    if (isLoading) {
                        return;
                    }

                    const remaining = list.scrollHeight - list.scrollTop - list.clientHeight;

                    if (remaining > 120) {
                        return;
                    }

                    isLoading = true;
                    @this.call('loadMoreConversations')
                        .finally(() => {
                            setTimeout(() => {
                                isLoading = false;
                            }, 120);
                        });
                }, { passive: true });
            };

            bindConversationInfiniteScroll();

            Livewire.hook('morphed', () => {
                bindConversationInfiniteScroll();
            });

            if (!window.__waComposerHotkeysBound) {
                window.__waComposerHotkeysBound = true;

                document.addEventListener('keydown', (event) => {
                    const target = event.target;
                    if (!(target instanceof HTMLTextAreaElement)) return;
                    if (!target.classList.contains('wa-textarea')) return;
                    if (event.isComposing) return;

                    if (event.key === 'Tab') {
                        const composer = target.closest('.wa-composer');
                        const suggestionList = composer?.querySelector('.wa-quick-suggestions');
                        const selectedButton = document.activeElement instanceof HTMLButtonElement
                            ? document.activeElement
                            : null;

                        const firstButton = suggestionList?.querySelector('[data-quick-response-id]');
                        const selectedInList = selectedButton && suggestionList?.contains(selectedButton)
                            ? selectedButton
                            : null;
                        const quickResponseButton = selectedInList || (firstButton instanceof HTMLButtonElement ? firstButton : null);

                        if (quickResponseButton) {
                            event.preventDefault();
                            applySuggestionToComposer(quickResponseButton);
                        }

                        return;
                    }

                    if (event.key !== 'Enter') return;

                    // Ctrl/Cmd + Enter: salto de linea.
                    if (event.ctrlKey || event.metaKey) return;

                    // Shift + Enter: salto de linea.
                    if (event.shiftKey) return;

                    event.preventDefault();

                    const sendButton = document.querySelector('[data-chat-send]');
                    if (sendButton instanceof HTMLButtonElement && !sendButton.disabled) {
                        sendButton.click();
                    }
                });

                document.addEventListener('mousedown', (event) => {
                    const button = event.target instanceof Element
                        ? event.target.closest('[data-quick-response-id]')
                        : null;

                    if (!(button instanceof HTMLButtonElement)) {
                        return;
                    }

                    event.preventDefault();
                    applySuggestionToComposer(button);
                });
            }

            if (!window.__waCopyButtonsBound) {
                window.__waCopyButtonsBound = true;

                const copyToClipboard = async (text) => {
                    if (!text) return false;

                    try {
                        await navigator.clipboard.writeText(text);
                        return true;
                    } catch (error) {
                        const temp = document.createElement('textarea');
                        temp.value = text;
                        temp.style.position = 'fixed';
                        temp.style.opacity = '0';
                        document.body.appendChild(temp);
                        temp.focus();
                        temp.select();
                        const copied = document.execCommand('copy');
                        document.body.removeChild(temp);
                        return copied;
                    }
                };

                document.addEventListener('click', async (event) => {
                    const button = event.target instanceof Element
                        ? event.target.closest('.wa-copy-chip[data-copy-value]')
                        : null;

                    if (!(button instanceof HTMLButtonElement)) {
                        return;
                    }

                    const value = button.getAttribute('data-copy-value') || '';
                    if (!value) {
                        return;
                    }

                    const original = button.getAttribute('data-copy-label-default') || button.textContent || 'Copiar';
                    const copiedLabel = button.getAttribute('data-copy-label-copied') || 'Copiado';
                    const success = await copyToClipboard(value);

                    if (!success) {
                        return;
                    }

                    button.classList.add('is-copied');
                    button.textContent = copiedLabel;

                    setTimeout(() => {
                        button.classList.remove('is-copied');
                        button.textContent = original;
                    }, 1300);
                });
            }

            // Cerrar sidebar al clickear el overlay
            document.querySelector('.wa-helpdesk')?.addEventListener('click', (e) => {
                if (e.target.classList.contains('wa-helpdesk') && e.target.classList.contains('wa-sidebar-open')) {
                    @this.set('mobilePane', 'chat');
                }
            });

            // Navegación por swipe horizontal en móvil
            if (!window.__waSwipeNavBound) {
                window.__waSwipeNavBound = true;

                let _swipeTouchStartX = 0;
                let _swipeTouchStartY = 0;
                const SWIPE_MIN = 60;

                document.addEventListener('touchstart', e => {
                    if (!e.target.closest('.wa-helpdesk')) return;
                    _swipeTouchStartX = e.touches[0].clientX;
                    _swipeTouchStartY = e.touches[0].clientY;
                }, { passive: true });

                document.addEventListener('touchend', e => {
                    if (!e.target.closest('.wa-helpdesk')) return;
                    if (window.innerWidth > 768) return;
                    if (e.target.closest('.wa-chat-actions, .wa-filters, .wa-textarea, .wa-composer')) return;

                    const dx = e.changedTouches[0].clientX - _swipeTouchStartX;
                    const dy = e.changedTouches[0].clientY - _swipeTouchStartY;

                    if (Math.abs(dy) > Math.abs(dx) * 0.8) return;
                    if (Math.abs(dx) < SWIPE_MIN) return;

                    const helpdesk = document.querySelector('.wa-helpdesk');
                    const pane = helpdesk?.dataset?.pane;
                    if (!pane) return;

                    if (dx < 0) { // swipe izquierda → avanzar
                        if (pane === 'chat') @this.set('mobilePane', 'profile');
                    } else { // swipe derecha → regresar
                        if (pane === 'chat') @this.call('backToList');
                        else if (pane === 'profile') @this.set('mobilePane', 'chat');
                    }
                }, { passive: true });
            }
        });
    </script>
</div>
