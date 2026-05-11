<?php
$chat_role = $_SESSION['user_role'] ?? null;
$chat_uid  = $_SESSION['user_id'] ?? null;
if (!$chat_uid || !$chat_role || $chat_role === 'admin' && false) : ?>

<?php endif; ?>

<?php if ($chat_uid) : ?>
<link rel="stylesheet" href="css/chat.css">

<!-- ── CHAT BUBBLE BUTTON -->
<button class="chat-bubble-btn" id="chatBubbleBtn" aria-label="Chat">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
  </svg>
  <span class="chat-unread-badge" id="chatUnreadBadge">0</span>
</button>

<!-- ── CHAT MODAL -->
<div class="chat-modal" id="chatModal">

  <!-- USER VIEW -->
  <?php if ($chat_role === 'customer') : ?>
  <div class="chat-header">
    <div class="chat-header-avatar">A</div>
    <div class="chat-header-info">
      <div class="chat-header-name">ArmiePrints Support</div>
      <div class="chat-header-status">We usually reply instantly</div>
    </div>
    <button class="chat-close-btn" id="chatCloseBtn">✕</button>
  </div>
  <div class="chat-messages" id="chatMessages">
    <span class="chat-empty">Send us a message!</span>
  </div>
  <div class="chat-input-area">
    <input type="text" class="chat-input" id="chatInput" placeholder="Type a message...">
    <button class="chat-send-btn" id="chatSendBtn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
    </button>
  </div>

  <!-- ADMIN VIEW -->
  <?php elseif ($chat_role === 'admin') : ?>
  <div class="chat-header" id="chatHeader">
    <div class="chat-header-avatar" id="chatHeaderAvatar">💬</div>
    <div class="chat-header-info">
      <div class="chat-header-name" id="chatHeaderName">Messages</div>
      <div class="chat-header-status" id="chatHeaderStatus">Select a conversation</div>
    </div>
    <button class="chat-close-btn" id="chatCloseBtn">✕</button>
  </div>

  <!-- User list -->
  <div class="chat-user-list" id="chatUserList"></div>

  <!-- Conversation (hidden initially) -->
  <div class="chat-messages" id="chatMessages" style="display:none; background:#f7f8fb;"></div>
  <div class="chat-input-area" id="chatInputArea" style="display:none;">
    <input type="text" class="chat-input" id="chatInput" placeholder="Reply...">
    <button class="chat-send-btn" id="chatSendBtn">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
      </svg>
    </button>
  </div>
  <?php endif; ?>

</div>

<script>
  const CHAT_ROLE    = '<?= $chat_role ?>';
  const CHAT_USER_ID = <?= $chat_uid ?>;
</script>
<script src="js/chat.js"></script>
<?php endif; ?>