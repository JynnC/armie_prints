document.addEventListener('DOMContentLoaded', () => {

  const bubbleBtn   = document.getElementById('chatBubbleBtn');
  const modal       = document.getElementById('chatModal');
  const closeBtn    = document.getElementById('chatCloseBtn');
  const messagesDiv = document.getElementById('chatMessages');
  const inputEl     = document.getElementById('chatInput');
  const sendBtn     = document.getElementById('chatSendBtn');
  const unreadBadge = document.getElementById('chatUnreadBadge');

  if (!bubbleBtn || !modal) return;

  let isOpen         = false;
  let pollInterval   = null;
  let activeUserId   = null; // admin only

  // ── OPEN / CLOSE
  function openChat() {
    isOpen = true;
    modal.classList.add('open');
    if (CHAT_ROLE === 'customer') {
      fetchMessages();
      startPolling();
    } else {
      fetchUserList();
      startAdminPolling();
    }
  }

  function closeChat() {
    isOpen = false;
    modal.classList.remove('open');
    stopPolling();
  }

  bubbleBtn.addEventListener('click', () => isOpen ? closeChat() : openChat());
  closeBtn?.addEventListener('click', closeChat);

  // ── SEND MESSAGE
  function sendMessage() {
    const msg = inputEl?.value.trim();
    if (!msg) return;

    const body = new URLSearchParams({ action: 'send', message: msg });
    if (CHAT_ROLE === 'admin' && activeUserId) body.append('user_id', activeUserId);

    fetch('chat.php', { method: 'POST', body })
      .then(r => r.json())
      .then(data => {
        if (data.status === 'success') {
          inputEl.value = '';
          fetchMessages(activeUserId);
        }
      });
  }

  sendBtn?.addEventListener('click', sendMessage);
  inputEl?.addEventListener('keydown', e => { if (e.key === 'Enter') sendMessage(); });

  // ── FETCH MESSAGES (customer)
  function fetchMessages(userId = null) {
    const url = userId
      ? `chat.php?action=fetch&user_id=${userId}`
      : `chat.php?action=fetch`;

    fetch(url)
      .then(r => r.json())
      .then(msgs => renderMessages(msgs));
  }

  // ── RENDER MESSAGES
  function renderMessages(msgs) {
    if (!messagesDiv) return;
    if (!msgs.length) {
      messagesDiv.innerHTML = '<span class="chat-empty">No messages yet. Say hi! 👋</span>';
      return;
    }

    messagesDiv.innerHTML = msgs.map(m => {
      const mine = (CHAT_ROLE === 'customer' && m.sender_role === 'customer') ||
                   (CHAT_ROLE === 'admin'    && m.sender_role === 'admin');
      const time = new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      return `
        <div class="chat-bubble ${mine ? 'mine' : 'theirs'}">
          ${escHtml(m.message)}
          <span class="chat-bubble-time">${time}</span>
        </div>`;
    }).join('');

    messagesDiv.scrollTop = messagesDiv.scrollHeight;
  }

  // ── UNREAD BADGE
  function fetchUnread() {
    fetch('chat.php?action=unread')
      .then(r => r.json())
      .then(data => {
        const count = data.count || 0;
        unreadBadge.textContent = count;
        unreadBadge.style.display = count > 0 ? 'flex' : 'none';
      });
  }

  // ── POLLING
  function startPolling() {
    stopPolling();
    pollInterval = setInterval(() => {
      fetchMessages(activeUserId);
      fetchUnread();
    }, 3000);
  }

  function startAdminPolling() {
    stopPolling();
    pollInterval = setInterval(() => {
      if (activeUserId) fetchMessages(activeUserId);
      else fetchUserList();
      fetchUnread();
    }, 3000);
  }

  function stopPolling() {
    if (pollInterval) { clearInterval(pollInterval); pollInterval = null; }
  }

  // ── ADMIN: USER LIST
  function fetchUserList() {
    fetch('chat.php?action=users')
      .then(r => r.json())
      .then(users => renderUserList(users));
  }

  function renderUserList(users) {
    const list = document.getElementById('chatUserList');
    if (!list) return;

    if (!users.length) {
      list.innerHTML = '<div style="text-align:center;padding:32px;color:#aaa;font-size:13px;">No conversations yet</div>';
      return;
    }

    list.innerHTML = users.map(u => `
      <div class="chat-user-item" data-uid="${u.id}">
        <div class="chat-user-avatar">${escHtml(u.full_name.charAt(0).toUpperCase())}</div>
        <div class="chat-user-info">
          <div class="chat-user-name">${escHtml(u.full_name)}</div>
          <div class="chat-user-preview">${escHtml(u.last_message || 'No messages yet')}</div>
        </div>
        ${u.unread > 0 ? `<div class="chat-user-unread">${u.unread}</div>` : ''}
      </div>
    `).join('');

    list.querySelectorAll('.chat-user-item').forEach(item => {
      item.addEventListener('click', () => openConversation(
        parseInt(item.dataset.uid),
        users.find(u => u.id == item.dataset.uid)?.full_name || 'User'
      ));
    });
  }

  // ── ADMIN: OPEN CONVERSATION
  function openConversation(userId, userName) {
    activeUserId = userId;

    const userList    = document.getElementById('chatUserList');
    const inputArea   = document.getElementById('chatInputArea');
    const headerName  = document.getElementById('chatHeaderName');
    const headerSub   = document.getElementById('chatHeaderStatus');
    const headerAvatar= document.getElementById('chatHeaderAvatar');
    const backBtn     = document.getElementById('chatBackBtn');

    if (userList)     userList.style.display   = 'none';
    if (messagesDiv)  messagesDiv.style.display = 'flex';
    if (inputArea)    inputArea.style.display   = 'flex';
    if (headerName)   headerName.textContent    = userName;
    if (headerSub)    headerSub.textContent      = 'Customer';
    if (headerAvatar) headerAvatar.textContent   = userName.charAt(0).toUpperCase();

    // Add back button if not already there
    if (!document.getElementById('chatBackBtn')) {
      const btn = document.createElement('button');
      btn.className  = 'chat-back-btn';
      btn.id         = 'chatBackBtn';
      btn.textContent = '←';
      btn.addEventListener('click', backToUserList);
      document.getElementById('chatHeader')?.insertBefore(btn, headerAvatar ?? null);
    }

    fetchMessages(userId);
    startAdminPolling();
  }

  // ── ADMIN: BACK TO LIST
  function backToUserList() {
    activeUserId = null;

    const userList  = document.getElementById('chatUserList');
    const inputArea = document.getElementById('chatInputArea');
    const backBtn   = document.getElementById('chatBackBtn');
    const headerName  = document.getElementById('chatHeaderName');
    const headerSub   = document.getElementById('chatHeaderStatus');
    const headerAvatar= document.getElementById('chatHeaderAvatar');

    if (userList)     userList.style.display   = 'block';
    if (messagesDiv)  messagesDiv.style.display = 'none';
    if (inputArea)    inputArea.style.display   = 'none';
    if (backBtn)      backBtn.remove();
    if (headerName)   headerName.textContent    = 'Messages';
    if (headerSub)    headerSub.textContent      = 'Select a conversation';
    if (headerAvatar) headerAvatar.textContent   = '💬';

    fetchUserList();
    startAdminPolling();
  }

  // ── ESCAPE HTML
  function escHtml(str) {
    return String(str)
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;');
  }

  // ── INIT: poll unread even when closed
  fetchUnread();
  setInterval(fetchUnread, 5000);

});