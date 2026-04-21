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
        
        * {
            box-sizing: border-box;
        }
        
        .wa-helpdesk {
            height: calc(100vh - 74px);
            min-height: 620px;
            display: grid;
            grid-template-columns: minmax(320px, 380px) minmax(480px, 1fr) minmax(280px, 360px);
            background: var(--wa-bg);
            color: var(--wa-text);
            font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
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
            padding: var(--wa-space-4);
            border-bottom: 1px solid var(--wa-border);
            display: flex;
            flex-direction: column;
            gap: var(--wa-space-3);
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
        
        .wa-filter.active {
            background: var(--wa-panel);
            color: var(--wa-accent);
            font-weight: 600;
            box-shadow: var(--wa-shadow-sm);
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
            padding: var(--wa-space-4);
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
            width: 40px;
            height: 40px;
            border-radius: var(--wa-radius-full);
            background: var(--wa-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 15px;
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
            font-size: 14px;
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
            font-size: 13px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-top: 2px;
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
        
        .wa-item-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
        }
        
        .wa-operator {
            font-size: 11px;
            color: var(--wa-text-tertiary);
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
                height: calc(100vh - 58px);
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

         <div class="wa-list">
             @forelse($conversations as $conversation)
                 @php
                     $displayName = $conversation->cliente?->nombrecli
                         ?: $conversation->contactoCanal?->nombre_canal
                         ?: $conversation->contactoCanal?->telefono_normalizado
                         ?: $conversation->contactoCanal?->canal_user_id
                         ?: 'Contacto';
                     $number = $conversation->contactoCanal?->telefono_normalizado
                         ?: $conversation->contactoCanal?->canal_user_id
                         ?: $conversation->cliente?->telefonocli
                         ?: 'Sin numero';
                     $lastMessage = $conversation->ultimoMensaje;
                     $unread = (int) ($conversation->unread_count ?: $conversation->mensajes_no_leidos);
                     $initial = strtoupper(substr($displayName, 0, 1));
                 @endphp
                 <button wire:click="selectConversation({{ $conversation->idconv }})" class="wa-item {{ $activeConversationId === $conversation->idconv ? 'active' : '' }}">
                     <div class="wa-item-row">
                         <div class="wa-avatar">{{ $initial }}</div>
                         <div class="wa-conversation-content">
                             <div style="display: flex; justify-content: space-between; width: 100%; align-items: baseline;">
                                 <span class="wa-name">{{ $displayName }}</span>
                                 <span class="wa-time">{{ optional($conversation->last_message_at ?: $conversation->ultima_actividad)->format('H:i') }}</span>
                             </div>
                             <span class="wa-number">{{ $number }}</span>
                             <div class="wa-preview">
                                 {{ $lastMessage?->contenido ?: match($lastMessage?->tipo_contenido) {
                                     'imagen' => '📷 Imagen',
                                     'audio' => '🎤 Audio',
                                     'documento', 'archivo' => '📄 Documento',
                                     default => 'Sin mensajes',
                                 } }}
                             </div>
                         </div>
                     </div>
                     <div class="wa-item-footer">
                         <span class="wa-small">{{ $conversation->operadorAsignado?->nombreemp ?: 'Sin asignar' }}</span>
                         <div style="display: flex; gap: 6px; align-items: center;">
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

         @if($conversations->hasPages())
             <div class="wa-toolbar">
                 {{ $conversations->links() }}
             </div>
         @endif

         <div class="wa-toolbar" style="border-top: 1px solid var(--wa-border);">
             <button wire:click="$toggle('showSettingsModal')" class="wa-action" style="width: 100%;">⚙️ Configuración Chat</button>
         </div>
     </aside>

    <main class="wa-chat">
        @if($activeConversation)
            @php
                $activeName = $activeConversation->cliente?->nombrecli
                    ?: $activeConversation->contactoCanal?->nombre_canal
                    ?: $activeConversation->contactoCanal?->telefono_normalizado
                    ?: 'Contacto';
                $activeNumber = $activeConversation->contactoCanal?->telefono_normalizado
                    ?: $activeConversation->contactoCanal?->canal_user_id
                    ?: $activeConversation->cliente?->telefonocli
                    ?: 'Sin numero';
                $typingOperator = $activeConversation->operadorEscribiendo;
                $isTyping = $typingOperator
                    && $activeConversation->operator_typing_at
                    && $activeConversation->operator_typing_at->gt(now()->subSeconds(8))
                    && $typingOperator->idemp !== auth()->user()?->idemp;
                $clientInitial = strtoupper(substr($activeName, 0, 1));
            @endphp

            <header class="wa-chat-header">
                <div class="wa-chat-title-row">
                    <div class="wa-chat-info">
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <button wire:click="backToList" class="wa-icon-btn wa-back" type="button">←</button>
                            <div>
                                <h2 class="wa-chat-title">{{ $activeName }}</h2>
                                <div class="wa-chat-subtitle">{{ $activeNumber }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="wa-chat-actions">
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
                    </div>
                </div>

                @if($isTyping)
                    <div style="margin-top: 8px; color: var(--wa-text-secondary); font-size: 12px;">{{ $typingOperator->nombreemp }} está escribiendo...</div>
                @elseif($activeConversation->operadorAsignado && $activeConversation->operadorAsignado->idemp !== auth()->user()?->idemp)
                    <div style="margin-top: 8px; color: var(--wa-text-secondary); font-size: 12px;">Atendido por {{ $activeConversation->operadorAsignado->nombreemp }}</div>
                @endif
            </header>

            <section class="wa-messages" id="wa-messages">
                @php $lastDate = null; @endphp
                @foreach($messages as $message)
                    @php
                        $dateKey = optional($message->created_at)->format('Y-m-d');
                        $type = $message->tipo ?: $message->tipo_contenido;
                        $mediaUrl = $message->media_url ?: $message->archivo_url;
                    @endphp
                    @if($dateKey !== $lastDate)
                        <div class="wa-date-divider">{{ optional($message->created_at)->format('d/m/Y') }}</div>
                        @php $lastDate = $dateKey; @endphp
                    @endif
                    <article class="wa-message {{ $message->tipo_remitente }}">
                        <div class="wa-bubble">
                            @if($type === 'imagen' && $mediaUrl)
                                <img src="{{ $mediaUrl }}" class="wa-media" alt="Imagen recibida">
                            @elseif($type === 'audio' && $mediaUrl)
                                <audio controls src="{{ $mediaUrl }}"></audio>
                            @elseif(in_array($type, ['documento', 'archivo'], true) && $mediaUrl)
                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener noreferrer" style="color: inherit;">📄 Abrir documento</a>
                            @endif

                            @if($message->contenido !== '')
                                <div>{{ $message->contenido }}</div>
                            @endif
                        </div>
                        <div class="wa-message-time">
                            {{ optional($message->created_at)->format('H:i') }}
                            @if($message->tipo_remitente === 'empleado')
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
                        <textarea wire:model.defer="messageText" wire:keydown="markTyping" class="wa-textarea" placeholder="Escribe un mensaje..." rows="1"></textarea>
                        <button wire:click="sendText" class="wa-send" type="button">Enviar</button>
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
             @endphp
             <div class="wa-client-header">
                 <div class="wa-client-avatar">{{ $clientInitial }}</div>
                 <div class="wa-client-name">{{ $client?->nombrecli ?: $activeName }}</div>
                 <div class="wa-client-phone">{{ $client?->telefonocli ?: $activeNumber }}</div>
             </div>
             
             <div class="wa-panel-scroll">
                 <section class="wa-card">
                     <div class="wa-card-title">Información</div>
                     <div class="wa-card-value">Primer contacto: {{ \Carbon\Carbon::parse($firstContact)->format('d/m/Y H:i') }}</div>
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

                 <div style="margin-top: 8px;">
                     <button wire:click="$toggle('showSettingsModal')" class="wa-send" style="width: 100%;">Cerrar</button>
                 </div>
             </div>
         </div>
     </div>
     @endif

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('chat-scroll-bottom', () => {
                setTimeout(() => {
                    const container = document.getElementById('wa-messages');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                }, 100);
            });
            
            // Cerrar sidebar al clickear el overlay
            document.querySelector('.wa-helpdesk')?.addEventListener('click', (e) => {
                if (e.target.classList.contains('wa-helpdesk') && e.target.classList.contains('wa-sidebar-open')) {
                    @this.set('mobilePane', 'chat');
                }
            });
        });
    </script>
</div>
