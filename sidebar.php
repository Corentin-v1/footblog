<?php include_once 'session.php'; ?>
<!-- Bouton d'ouverture de la sidebar -->
<button id="openSidebarBtn" style="position:fixed;top:10px;left:16px;z-index:1200;padding:10px 14px;background:#007BFF;color:#fff;border:none;border-radius:6px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,0.13);">
  ☰
</button>
<aside class="sidebar" id="sidebar" style="transform:translateX(-220px);transition:transform 0.3s;background:rgba(255, 255, 255, 0.2);box-shadow:0 8px 32px rgba(0, 0, 0, 0.37);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);border-right:1px solid rgba(255, 255, 255, 0.18);">
  <button id="closeSidebarBtn" style="position:absolute;top:12px;right:12px;background:none;border:none;color:#fff;font-size:1.7em;cursor:pointer;">×</button>
  <nav>
    <ul style="padding:0;margin:0;">
      <li style="margin-bottom:14px;"><a href="index.php"><b>Accueil</b></a></li>
      <li style="margin-bottom:14px;"><a href="apres-match.php"><b>Article d'après match</b></a></li>
      <li style="margin-bottom:14px;"><a href="interview.php"><b>Interview</b></a></li>
      <li style="margin-bottom:14px;"><a href="accreditation.php"><b>Accréditation</b></a></li>
      <li style="margin-bottom:14px;"><a href="autre.php"><b>Autre article</b></a></li>
      <?php if (is_logged_in()): ?>
        <li style="margin-bottom:14px;"><a href="ajout.php"><b>Ajouter un article</b></a></li>
      <?php endif; ?>
    </ul>
  </nav>
  <div class="sidebar-social" style="position:absolute;bottom:18px;left:0;width:100%;display:flex;flex-direction:column;align-items:center;gap:10px;">
    <a href="https://www.linkedin.com/in/paulo-scalvinoni-67086a320/" target="_blank" class="sidebar-social-link" style="display:flex;align-items:center;gap:6px;">
      LinkedIn
      <img src="linkedin-logo-linkedin-icon-transparent-free-png.webp" alt="LinkedIn" class="sidebar-social-icon" style="width:18px;height:18px;object-fit:contain;">
    </a>
    <a href="https://x.com/Cycling701" target="_blank" class="sidebar-social-link" style="display:flex;align-items:center;gap:6px;">
      Twitter
      <img src="X-Logo-removebg-preview.png" alt="Twitter" class="sidebar-social-icon" style="width:18px;height:18px;object-fit:contain;">
    </a>
  </div>
</aside>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var sidebar = document.getElementById('sidebar');
  var openBtn = document.getElementById('openSidebarBtn');
  var closeBtn = document.getElementById('closeSidebarBtn');
  function openSidebar() {
    sidebar.style.transform = 'translateX(0)';
    openBtn.style.display = 'none';
  }
  function closeSidebar() {
    sidebar.style.transform = 'translateX(-220px)';
    openBtn.style.display = '';
  }
  openBtn.addEventListener('click', openSidebar);
  closeBtn.addEventListener('click', closeSidebar);
  // Fermer la sidebar si on clique en dehors (optionnel)
  document.addEventListener('click', function(e) {
    if (!sidebar.contains(e.target) && e.target !== openBtn && sidebar.style.transform === 'translateX(0)') {
      closeSidebar();
    }
  });
});
</script>