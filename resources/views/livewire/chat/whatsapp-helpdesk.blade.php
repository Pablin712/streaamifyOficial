<div class="wa-helpdesk {{ $mobilePane === 'list' ? 'wa-sidebar-open' : '' }}" wire:poll.3s="refreshChat">
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
            background: var(--wa-bg);
        }

        .wa-chat-header {
            background: var(--wa-panel);
            border-bottom: 1px solid var(--wa-border);
            padding: var(--wa-space-4);
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

        .wa-operator-panel {
            margin-top: var(--wa-space-3);
            padding: 12px;
            border-radius: var(--wa-radius-md);
            border: 1px solid var(--wa-border);
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .wa-operator-main {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--wa-text-secondary);
            font-size: 12px;
            font-weight: 600;
        }

        .wa-operator-value {
            color: var(--wa-text);
            font-size: 13px;
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
            gap: var(--wa-space-3);
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
            border-radius: var(--wa-radius-lg);
            padding: 10px 14px;
            background: var(--wa-panel);
            box-shadow: var(--wa-shadow-sm);
            max-width: 100%;
            word-wrap: break-word;
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
           PANEL DERECHO FICHA CLIENTE
           ================================================ */

        .wa-client-header {
            padding: var(--wa-space-5) var(--wa-space-4);
            background: var(--wa-panel);
            border-bottom: 1px solid var(--wa-border);
            text-align: center;
        }

        .wa-client-avatar {
            width: 64px;
            height: 64px;
            border-radius: var(--wa-radius-full);
            background: var(--wa-accent-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 24px;
            color: var(--wa-accent);
            margin: 0 auto 12px;
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
            padding: var(--wa-space-4);
            display: flex;
            flex-direction: column;
            gap: var(--wa-space-3);
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
            padding: 16px;
            border-radius: 18px;
            background: radial-gradient(circle at top left, #ffffff 0%, #eef4ff 45%, #f8fafc 100%);
            border: 1px solid rgba(148, 163, 184, 0.24);
            box-shadow: 0 18px 40px rgba(37, 99, 235, 0.08);
            display: grid;
            gap: 14px;
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
            font-size: 16px;
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

        .wa-profile-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .wa-profile-stat {
            padding: 12px;
            border-radius: 14px;
            background: rgba(255,255,255,0.76);
            border: 1px solid rgba(226, 232, 240, 0.9);
        }

        .wa-profile-stat-label {
            font-size: 11px;
            color: var(--wa-text-tertiary);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            font-weight: 700;
        }

        .wa-profile-stat-value {
            margin-top: 4px;
            font-size: 13px;
            font-weight: 700;
            color: var(--wa-text);
            word-break: break-word;
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

        .wa-tag {
            display: inline-flex;
            padding: 4px 8px;
            background: var(--wa-accent-soft);
            color: var(--wa-accent);
            border-radius: var(--wa-radius-sm);
            font-size: 12px;
            font-weight: 500;
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
            padding: 10px;
            display: grid;
            gap: 8px;
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
            gap: 6px;
            font-size: 12px;
            color: var(--wa-text-secondary);
        }

        .wa-copy-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
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

        .wa-empty {
            margin: auto;
            text-align: center;
            color: var(--wa-text-tertiary);
            padding: var(--wa-space-6);
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

        /* ================================================
           AJUSTES EXTRA DARK MODE (ALTO CONTRASTE)
           ================================================ */
        :root[data-dark-mode="true"] .wa-item.active {
            background: #1e2b3f;
            border-left-color: #60a5fa;
        }

        :root[data-dark-mode="true"] .wa-operator-panel {
            background: linear-gradient(180deg, #1a2331 0%, #151d2a 100%);
            border-color: #2a3240;
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

        :root[data-dark-mode="true"] .wa-profile-stat {
            background: rgba(23, 31, 44, 0.92);
            border-color: #2e3d52;
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

        :root[data-dark-mode="true"] .wa-operator-chip {
            background: #1f2836;
            color: #d8e2ef;
            border-color: #344154;
        }

        :root[data-dark-mode="true"] .wa-operator-chip::before {
            background: #7f95b3;
        }

        /* ================================================
           RESPONSIVE
           ================================================ */

        @media (max-width: 1180px) {
            .wa-helpdesk {
                grid-template-columns: 340px 1fr;
            }
            .wa-right {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .wa-helpdesk {
                position: relative;
                height: calc(100dvh - 58px);
                min-height: 0;
            }

            .wa-column:first-child {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                max-width: 320px;
                height: 100vh;
                z-index: 99;
                transform: translateX(-100%);
                transition: transform 220ms cubic-bezier(0.2, 0, 0.38, 0.9);
                box-shadow: 4px 0 24px rgba(0,0,0,0.12);
            }

            .wa-helpdesk.wa-sidebar-open .wa-column:first-child {
                transform: translateX(0);
            }

            .wa-helpdesk::after {
                content: '';
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.4);
                backdrop-filter: blur(2px);
                z-index: 98;
                pointer-events: none;
                opacity: 0;
                transition: opacity 180ms ease;
            }

            .wa-helpdesk.wa-sidebar-open::after {
                pointer-events: auto;
                opacity: 1;
            }

            .wa-chat {
                position: absolute;
                inset: 0;
                display: flex;
            }

            .wa-back {
                display: inline-flex;
            }

            .wa-messages {
                padding: var(--wa-space-4);
            }

            .wa-message {
                max-width: 80%;
            }

            .wa-toolbar {
                padding-top: 66px;
            }
        }
    </style>

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

             <input wire:model.live.debounce.300ms="search" class="wa-search" type="search" placeholder="Buscar cliente, nombre o numero">

             <div class="wa-filters">
                 @foreach([
                     'todos' => 'Todos',
                     'nuevas' => 'Nuevas',
                     'no_leidas' => 'No leídas',
                     'asignadas_mi' => 'Mías',
                     'abiertas' => 'Abiertas',
                     'cerradas' => 'Cerradas',
                 ] as $key => $label)
                     <button wire:click="$set('filter', '{{ $key }}')" class="wa-filter {{ $filter === $key ? 'active' : '' }}">
                         {{ $label }}
                     </button>
                 @endforeach
             </div>
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
                 @endphp
                 <button type="button" wire:key="conversation-{{ $conversation->idconv }}" wire:click="selectConversation({{ $conversation->idconv }})" class="wa-item {{ $activeConversationId === $conversation->idconv ? 'active' : '' }}">
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
                                 {{ $lastMessage?->contenido ?: match($lastMessage?->tipo_contenido) {
                                     'imagen' => '📷 Imagen',
                                     'audio' => '🎤 Audio',
                                     'sticker' => '🧩 Sticker',
                                     'documento', 'archivo' => '📄 Documento',
                                     'video' => '🎬 Video',
                                     default => 'Sin mensajes',
                                 } }}
                             </div>
                         </div>
                     </div>
                     <div class="wa-item-footer">
                         <span class="wa-operator-chip" title="{{ $conversation->operadorAsignado?->nombreemp ?: 'Sin asignar' }}">
                             {{ $conversation->operadorAsignado?->nombreemp ?: 'Sin asignar' }}
                         </span>
                         <div style="display: flex; gap: 6px; align-items: center;">
                             <span class="wa-badge {{ $contactIdentity['tone'] }}">{{ $contactIdentity['label'] }}</span>
                             @if($unread > 0)
                                 <span class="wa-badge danger">{{ $unread }}</span>
                             @endif
                         </div>
                     </div>
                 </button>
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
                    default => 'WhatsApp',
                };
                $assignedOperatorName = $activeConversation->operadorAsignado?->nombreemp ?? 'Sin asignar';
                $typingLabel = $isTyping ? ($typingOperator->nombreemp.' escribiendo...') : 'Sin actividad de escritura';
                $clientInitial = strtoupper(substr($activeName, 0, 1));
            @endphp

            <header class="wa-chat-header">
                <div class="wa-chat-title-row">
                    <div class="wa-chat-info">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <button wire:click="backToList" class="wa-icon-btn wa-back" type="button">←</button>
                            <div>
                                <h2 class="wa-chat-title">{{ $activeName }}</h2>
                                <div class="wa-chat-subtitle wa-channel-meta">
                                    <span>{{ $activeNumber }}</span>
                                    <span class="wa-channel-label">
                                        <span class="wa-channel-dot {{ $activeChannelColor }}"></span>
                                        {{ $activeChannelLabel }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wa-chat-actions">
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

                <div class="wa-operator-panel">
                    <div class="wa-operator-main">
                        Operador
                        <span class="wa-operator-value">{{ $assignedOperatorName }}</span>
                    </div>
                    <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                        <span class="wa-badge {{ $isTyping ? 'success' : 'muted' }}">{{ $typingLabel }}</span>
                        <span class="wa-badge muted">{{ $messagesLoaded }} mensajes cargados</span>
                    </div>
                </div>

                <div class="wa-chat-search">
                    <input wire:model.live.debounce.300ms="activeMessageSearch" class="wa-search" type="search" placeholder="Buscar dentro de esta conversación">
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
                    @endphp
                    @if($dateKey !== $lastDate)
                        <div class="wa-date-divider">{{ optional($message->created_at)->format('d/m/Y') }}</div>
                        @php $lastDate = $dateKey; @endphp
                    @endif
                    <article class="wa-message {{ $message->tipo_remitente }}">
                        <div class="wa-bubble">
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
                        </div>
                        <div class="wa-message-time">
                            {{ optional($message->created_at)->format('H:i') }}
                            @if(in_array($message->tipo_remitente, ['empleado', 'ia'], true))
                                · {{ $message->error_message ? '⚠️ Error' : ($message->delivered_at ? '✓✓ Enviado' : '✓ Pendiente') }}
                            @endif
                        </div>
                    </article>
                @endforeach
            </section>

            <footer class="wa-composer">
                @error('messageText') <span style="color: var(--wa-danger); font-size: 12px; display: block; margin-bottom: 8px;">{{ $message }}</span> @enderror
                @error('imageUpload') <span style="color: var(--wa-danger); font-size: 12px; display: block; margin-bottom: 8px;">{{ $message }}</span> @enderror
                @error('audioUpload') <span style="color: var(--wa-danger); font-size: 12px; display: block; margin-bottom: 8px;">{{ $message }}</span> @enderror

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

                <div class="wa-compose-row">
                    @if($settings['chat_allow_image'])
                        <label class="wa-attach-btn" title="Adjuntar imagen">
                            📷
                            <input wire:model="imageUpload" class="wa-file-input" type="file" accept="image/*">
                        </label>
                    @endif

                    @if($settings['chat_allow_audio'])
                        <label class="wa-attach-btn" title="Subir audio">
                            🎤
                            <input wire:model="audioUpload" class="wa-file-input" type="file" accept="audio/*">
                        </label>
                    @endif

                    @if($settings['chat_allow_text'])
                        <textarea wire:model.live="messageText" wire:keydown="markTyping" class="wa-textarea" placeholder="Escribe un mensaje..." rows="1"></textarea>
                        <button wire:click="sendText" class="wa-send" type="button" data-chat-send>Enviar</button>
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
            </div>
        @endif
    </main>

     <aside class="wa-column wa-right">
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
                 <div class="wa-profile-grid">
                     <div class="wa-profile-stat">
                         <div class="wa-profile-stat-label">Primer contacto</div>
                         <div class="wa-profile-stat-value">{{ \Carbon\Carbon::parse($firstContact)->format('d/m/Y H:i') }}</div>
                     </div>
                     <div class="wa-profile-stat">
                         <div class="wa-profile-stat-label">Asignación</div>
                         <div class="wa-profile-stat-value">{{ $assignedOperatorName }}</div>
                     </div>
                 </div>
                 <div>
                     <div class="wa-card-title" style="margin-bottom: 8px;">Clasificación del contacto</div>
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
                 </div>
             </div>

             <div class="wa-panel-scroll">
                 <section class="wa-card">
                     <div class="wa-card-title">Información</div>
                     <div class="wa-card-value">Origen: {{ $activeConversation->origen ?: 'WhatsApp' }}</div>
                     <div style="font-size: 12px; color: var(--wa-text-secondary); margin-top: 8px;">ID canal: {{ $activeConversation->contactoCanal?->canal_user_id ?: 'Sin dato' }}</div>
                 </section>

                 <section class="wa-card">
                     <div class="wa-card-title">Usuarios activos del cliente ({{ $clientActiveUsers->count() }})</div>

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
                 </section>

                 <section class="wa-card">
                     <div class="wa-card-title">Historial de compras</div>
                     @forelse($client?->ventas?->take(6) ?? [] as $sale)
                         <div class="wa-purchase-item">
                             <span>{{ optional($sale->fechaven)->format('d/m/Y') ?: $sale->idven }}</span>
                             <span style="font-weight: 600;">${{ number_format((float) $sale->totalpagoven, 2) }}</span>
                         </div>
                     @empty
                         <span style="color: var(--wa-text-secondary); font-size: 13px;">Sin compras registradas.</span>
                     @endforelse
                 </section>

                 <section class="wa-card">
                     <div class="wa-card-title">Notas internas</div>
                     <span style="color: var(--wa-text-secondary); font-size: 13px;">{{ data_get($activeConversation->metadata, 'notas') ?: 'Sin notas.' }}</span>
                 </section>

                 <section class="wa-card">
                     <div class="wa-card-title">Tags</div>
                     <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                         @forelse((array) data_get($activeConversation->metadata, 'tags', []) as $tag)
                             <span class="wa-tag">{{ $tag }}</span>
                         @empty
                             <span style="color: var(--wa-text-secondary); font-size: 13px;">Sin tags.</span>
                         @endforelse
                     </div>
                 </section>
             </div>
         @else
             <div class="wa-empty">
                 <div style="font-size: 48px; margin-bottom: 16px;">👤</div>
                 <span style="color: var(--wa-text-tertiary);">Selecciona una conversación para ver la ficha del cliente</span>
             </div>
         @endif
     </aside>

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
                                         <span style="width: 10px; height: 10px; border-radius: 9999px; display: inline-block; background: {{ $channel->color === 'verde' ? '#10b981' : ($channel->color === 'azul' ? '#2563eb' : '#64748b') }};"></span>
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
        });
    </script>
</div>
