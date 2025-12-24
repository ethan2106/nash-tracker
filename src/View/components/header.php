<header class="relative z-[var(--z-header)] bg-gradient-to-r from-green-200 to-blue-200 shadow-xl py-4 lg:py-6 mb-8 lg:mb-12 rounded-b-3xl backdrop-blur-md border-b border-blue-100 overflow-visible">
  <?php
  // Session include sécurisé (DOIT être en premier pour $SESSION disponible partout)
  $sessionPath = __DIR__ . '/../../Config/session.php';
  if (file_exists($sessionPath))
  {
      require_once $sessionPath;
  }
  ?>
  
  <div class="container mx-auto flex items-center justify-between px-4 lg:px-6">

    <!-- TITRE / LOGO A GAUCHE -->
    <div class="flex items-center gap-3">
      <a href="?" class="flex items-center gap-3 text-slate-800 no-underline">
        <i class="fa-solid fa-leaf text-green-500 text-2xl lg:text-3xl" aria-hidden="true"></i>
        <span class="text-lg lg:text-xl font-bold">Suivi <span class="text-green-600">Nash</span></span>
      </a>
    </div>

    <!-- BOUTON HAMBURGER (Mobile uniquement) -->
    <button type="button" 
            id="mobile-menu-btn"
            class="lg:hidden flex items-center justify-center w-10 h-10 rounded-xl bg-white/90 hover:bg-blue-100 text-slate-700 shadow-md border border-blue-100 transition-all"
            aria-label="Ouvrir le menu"
            aria-expanded="false"
            aria-controls="mobile-menu">
      <i class="fa-solid fa-bars text-xl" aria-hidden="true" id="hamburger-icon"></i>
      <i class="fa-solid fa-xmark text-xl hidden" aria-hidden="true" id="close-icon"></i>
    </button>

    <!-- NAV DROITE (Desktop - caché sur mobile) -->
    <nav class="hidden lg:flex items-center gap-3">

      <?php if (isset($_SESSION['user']))
      { ?>

        <!-- MENU Alimentaire (VISIBLE UNIQUEMENT CONNECTÉ) -->
        <div class="relative dropdown-wrapper z-50">
          <button type="button"
                  class="dropdown-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-white/90 hover:bg-green-100 text-green-700 font-semibold shadow-md transition-all border border-green-100"
                  aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-utensils text-green-500" aria-hidden="true"></i>
            <span>Alimentaire</span>
            <i class="fa-solid fa-chevron-down text-gray-500 text-sm transition-transform duration-300" aria-hidden="true"></i>
          </button>

          <div class="dropdown-menu absolute right-0 top-full mt-2 w-48 bg-white/95 rounded-xl shadow-xl border border-green-100 py-2 opacity-0 pointer-events-none transform translate-y-2 transition-all duration-200 origin-top-right z-[var(--z-modal)]"
               role="menu" aria-hidden="true">
            <div class="flex flex-col gap-1 px-2">
              <a href="?page=food" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-green-50 text-green-700 font-semibold shadow-sm transition-all border border-green-100">
                <i class="fa-solid fa-plus text-green-400" aria-hidden="true"></i><span>Ajouter un aliment</span>
              </a>
              <a href="?page=catalog" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-orange-50 text-orange-700 font-semibold shadow-sm transition-all border border-orange-100">
                <i class="fa-solid fa-book text-orange-400" aria-hidden="true"></i><span>Catalogue</span>
              </a>
              <a href="?page=meals" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-purple-50 text-purple-700 font-semibold shadow-sm transition-all border border-purple-100">
                <i class="fa-solid fa-utensils text-purple-400" aria-hidden="true"></i><span>Mes Repas</span>
              </a>
            </div>
          </div>
        </div>

        <!-- MENU Suivi Santé (VISIBLE UNIQUEMENT CONNECTÉ) -->
        <div class="relative dropdown-wrapper z-40">
          <button type="button"
                  class="dropdown-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-white/90 hover:bg-blue-100 text-blue-700 font-semibold shadow-md transition-all border border-blue-100"
                  aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-heartbeat text-blue-400" aria-hidden="true"></i>
            <span>Suivi Santé</span>
            <i class="fa-solid fa-chevron-down text-gray-500 text-sm transition-transform duration-300" aria-hidden="true"></i>
          </button>

          <div class="dropdown-menu absolute right-0 top-full mt-2 w-48 bg-white/95 rounded-xl shadow-xl border border-blue-100 py-2 opacity-0 pointer-events-none transform translate-y-2 transition-all duration-200 origin-top-right z-[var(--z-modal)]"
               role="menu" aria-hidden="true">
            <div class="flex flex-col gap-1 px-2">
              <a href="?page=imc" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-red-50 text-red-700 font-semibold shadow-sm transition-all border border-red-100">
                <i class="fa-solid fa-weight-scale text-red-400" aria-hidden="true"></i><span>Calcul IMC</span>
              </a>
              <a href="?page=medicaments" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-blue-50 text-blue-700 font-semibold shadow-sm transition-all border border-blue-100">
                <i class="fa-solid fa-pills text-blue-400" aria-hidden="true"></i><span>Médicaments</span>
              </a>
              <a href="?page=activity" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-green-50 text-green-700 font-semibold shadow-sm transition-all border border-green-100">
                <i class="fa-solid fa-person-running text-green-400" aria-hidden="true"></i><span>Activités Physiques</span>
              </a>
              <a href="?page=walktrack" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-emerald-50 text-emerald-700 font-semibold shadow-sm transition-all border border-emerald-100">
                <i class="fa-solid fa-person-walking text-emerald-400" aria-hidden="true"></i><span>WalkTrack</span>
              </a>
              <a href="?page=reports" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-indigo-50 text-indigo-700 font-semibold shadow-sm transition-all border border-indigo-100">
                <i class="fa-solid fa-file-pdf text-indigo-400" aria-hidden="true"></i><span>Rapports</span>
              </a>
            </div>
          </div>
        </div>

        <!-- MENU PROFIL -->
        <?php
        if (empty($_SESSION['csrf_token_login']))
        {
            $_SESSION['csrf_token_login'] = bin2hex(random_bytes(24));
        }
          require_once __DIR__ . '/csrf_logout_link.php';
          $pseudo = htmlspecialchars($_SESSION['user']['pseudo']);
          ?>

        <div class="relative dropdown-wrapper z-60">
          <button type="button"
                  class="dropdown-btn flex items-center gap-2 px-4 py-2 rounded-xl bg-white/90 hover:bg-blue-100 text-blue-700 font-semibold shadow-md border border-blue-100"
                  aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-user-circle text-blue-500 text-xl" aria-hidden="true"></i>
            <span>Salut, <?= $pseudo; ?></span>
            <i class="fa-solid fa-chevron-down text-gray-500 text-sm transition-transform duration-300" aria-hidden="true"></i>
          </button>

          <div class="dropdown-menu absolute right-0 top-full mt-2 w-44 bg-white/95 rounded-xl shadow-xl border border-blue-100 py-2 opacity-0 pointer-events-none transform translate-y-2 transition-all duration-200 origin-top-right z-[var(--z-modal)]"
               role="menu" aria-hidden="true">
            <div class="flex flex-col gap-2 px-2">
              <a href="?page=profile" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-blue-50 text-blue-700 font-semibold shadow-sm transition-all border border-blue-100">
                <i class="fa-solid fa-id-card text-blue-400" aria-hidden="true"></i><span>Profil</span>
              </a>
              <a href="?page=settings" role="menuitem" tabindex="0"
                 class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-gray-50 text-gray-700 font-semibold shadow-sm transition-all border border-gray-100">
                <i class="fa-solid fa-gear text-gray-400" aria-hidden="true"></i><span>Paramètres</span>
              </a>
              <div class="border-t border-gray-200 my-1"></div>
              <?= csrf_logout_link('Déconnexion', 'flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-red-50 text-red-500 font-semibold shadow-sm transition-all border border-red-100 min-w-[100px] justify-center cursor-pointer'); ?>
            </div>
          </div>
        </div>

      <?php } else
      { ?>

        <!-- SI NON CONNECTÉ : seulement Inscription / Connexion -->
        <a href="?page=register"
           class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/60 hover:bg-green-100 text-green-700 font-semibold shadow-md border border-green-100">
          <i class="fa-solid fa-user-plus text-green-400" aria-hidden="true"></i> Inscription
        </a>

        <a href="?page=login"
           class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-blue-400 to-green-400 text-white font-bold shadow-xl border border-white/30">
          <i class="fa-solid fa-right-to-bracket text-white" aria-hidden="true"></i> Connexion
        </a>

      <?php } ?>

    </nav>
  </div>
</header>

<!-- ============================================================
     MENU MOBILE (Overlay plein écran) - EN DEHORS DU HEADER
     - Contrôlé par JavaScript (toggle avec hamburger)
     ============================================================ -->
<div id="mobile-menu" 
     class="lg:hidden fixed inset-0 bg-gradient-to-br from-green-100 via-blue-100 to-purple-100 z-[var(--z-overlay)] overflow-y-auto opacity-0 pointer-events-none transition-opacity duration-200"
     aria-hidden="true">
  <div class="min-h-full">
      
      <!-- Header du menu mobile avec bouton fermer -->
      <div class="flex items-center justify-between px-4 py-4 bg-gradient-to-r from-green-200 to-blue-200 border-b border-blue-100">
        <a href="?" class="flex items-center gap-2 text-slate-800 no-underline">
          <i class="fa-solid fa-leaf text-green-500 text-2xl" aria-hidden="true"></i>
          <span class="text-lg font-bold">Suivi <span class="text-green-600">Nash</span></span>
        </a>
        <button type="button" 
                id="mobile-menu-close"
                class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/90 hover:bg-red-100 text-slate-700 shadow-md border border-red-200 transition-all"
                aria-label="Fermer le menu">
          <i class="fa-solid fa-xmark text-xl" aria-hidden="true"></i>
        </button>
      </div>
      
      <!-- Contenu du menu -->
      <div class="px-4 py-6">
      
      <?php if (isset($_SESSION['user']))
      { ?>
        
        <!-- User info mobile -->
        <div class="flex items-center gap-3 mb-6 pb-4 border-b border-white/50">
          <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center shadow-lg">
            <i class="fa-solid fa-user text-white text-xl" aria-hidden="true"></i>
          </div>
          <div>
            <p class="font-bold text-gray-800">Salut, <?= htmlspecialchars($_SESSION['user']['pseudo']); ?></p>
            <p class="text-sm text-gray-600"><?= htmlspecialchars($_SESSION['user']['email'] ?? ''); ?></p>
          </div>
        </div>

        <!-- Section Alimentaire -->
        <div class="mb-6">
          <h3 class="text-sm font-bold text-green-700 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fa-solid fa-utensils" aria-hidden="true"></i> Alimentaire
          </h3>
          <div class="space-y-2">
            <a href="?page=food" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-green-700 font-semibold shadow-sm transition-all border border-green-100">
              <i class="fa-solid fa-plus text-green-500" aria-hidden="true"></i> Ajouter un aliment
            </a>
            <a href="?page=catalog" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-orange-700 font-semibold shadow-sm transition-all border border-orange-100">
              <i class="fa-solid fa-book text-orange-500" aria-hidden="true"></i> Catalogue
            </a>
            <a href="?page=meals" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-purple-700 font-semibold shadow-sm transition-all border border-purple-100">
              <i class="fa-solid fa-utensils text-purple-500" aria-hidden="true"></i> Mes Repas
            </a>
          </div>
        </div>

        <!-- Section Suivi Santé -->
        <div class="mb-6">
          <h3 class="text-sm font-bold text-blue-700 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fa-solid fa-heartbeat" aria-hidden="true"></i> Suivi Santé
          </h3>
          <div class="space-y-2">
            <a href="?page=imc" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-red-700 font-semibold shadow-sm transition-all border border-red-100">
              <i class="fa-solid fa-weight-scale text-red-500" aria-hidden="true"></i> Calcul IMC
            </a>
            <a href="?page=medicaments" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-blue-700 font-semibold shadow-sm transition-all border border-blue-100">
              <i class="fa-solid fa-pills text-blue-500" aria-hidden="true"></i> Médicaments
            </a>
            <a href="?page=activity" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-green-700 font-semibold shadow-sm transition-all border border-green-100">
              <i class="fa-solid fa-person-running text-green-500" aria-hidden="true"></i> Activités Physiques
            </a>
            <a href="?page=reports" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-indigo-700 font-semibold shadow-sm transition-all border border-indigo-100">
              <i class="fa-solid fa-file-pdf text-indigo-500" aria-hidden="true"></i> Rapports
            </a>
          </div>
        </div>

        <!-- Section Compte -->
        <div class="mb-6">
          <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2">
            <i class="fa-solid fa-user-circle" aria-hidden="true"></i> Mon Compte
          </h3>
          <div class="space-y-2">
            <a href="?page=profile" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-blue-700 font-semibold shadow-sm transition-all border border-blue-100">
              <i class="fa-solid fa-id-card text-blue-500" aria-hidden="true"></i> Profil
            </a>
            <a href="?page=settings" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-white/70 hover:bg-white text-gray-700 font-semibold shadow-sm transition-all border border-gray-200">
              <i class="fa-solid fa-gear text-gray-500" aria-hidden="true"></i> Paramètres
            </a>
          </div>
        </div>

        <!-- Bouton Déconnexion -->
        <div class="pt-4 border-t border-white/50">
          <?php require_once __DIR__ . '/csrf_logout_link.php'; ?>
          <?= csrf_logout_link('Déconnexion', 'flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold shadow-lg transition-all'); ?>
        </div>

      <?php } else
      { ?>

        <!-- Non connecté - Inscription / Connexion -->
        <div class="space-y-4 pt-4">
          <a href="?page=register"
             class="flex items-center justify-center gap-2 w-full px-4 py-4 rounded-xl bg-white/80 hover:bg-white text-green-700 font-bold shadow-lg border border-green-200 transition-all">
            <i class="fa-solid fa-user-plus text-green-500" aria-hidden="true"></i> Inscription
          </a>
          <a href="?page=login"
             class="flex items-center justify-center gap-2 w-full px-4 py-4 rounded-xl bg-gradient-to-r from-blue-500 to-green-500 text-white font-bold shadow-lg transition-all hover:shadow-xl">
            <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Connexion
          </a>
        </div>

      <?php } ?>
      
      </div><!-- fin contenu menu -->
    </div>
  </div>

  <!-- SCRIPT amélioré : hover + click toggle + clavier (Escape) + Mobile menu -->
  <script>
    (function() {
      // ============================================================
      // DROPDOWN MENUS (Desktop)
      // ============================================================
      function closeMenu(menu, btn) {
        if (!menu) return;
        menu.classList.remove('opacity-100', 'pointer-events-auto', 'translate-y-0');
        menu.classList.add('opacity-0', 'pointer-events-none', 'translate-y-2');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        if (menu) menu.setAttribute('aria-hidden', 'true');
      }
      function openMenu(menu, btn) {
        if (!menu) return;
        // fermer les autres menus
        document.querySelectorAll('.dropdown-menu.opacity-100').forEach(function(m) {
          if (m !== menu) {
            const b = m.closest('.dropdown-wrapper')?.querySelector('.dropdown-btn');
            closeMenu(m, b);
          }
        });
        menu.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-2');
        menu.classList.add('opacity-100', 'pointer-events-auto', 'translate-y-0');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        if (menu) menu.setAttribute('aria-hidden', 'false');
      }

      // ============================================================
      // MOBILE MENU
      // ============================================================
      function closeMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');
        
        if (mobileMenu) {
          mobileMenu.classList.add('opacity-0', 'pointer-events-none');
          mobileMenu.classList.remove('opacity-100', 'pointer-events-auto');
          mobileMenu.setAttribute('aria-hidden', 'true');
        }
        if (mobileBtn) mobileBtn.setAttribute('aria-expanded', 'false');
        if (hamburgerIcon) hamburgerIcon.classList.remove('hidden');
        if (closeIcon) closeIcon.classList.add('hidden');
        document.body.style.overflow = '';
      }

      function openMobileMenu() {
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const hamburgerIcon = document.getElementById('hamburger-icon');
        const closeIcon = document.getElementById('close-icon');
        
        if (mobileMenu) {
          mobileMenu.classList.remove('opacity-0', 'pointer-events-none');
          mobileMenu.classList.add('opacity-100', 'pointer-events-auto');
          mobileMenu.setAttribute('aria-hidden', 'false');
        }
        if (mobileBtn) mobileBtn.setAttribute('aria-expanded', 'true');
        if (hamburgerIcon) hamburgerIcon.classList.add('hidden');
        if (closeIcon) closeIcon.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      }

      function toggleMobileMenu() {
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const isOpen = mobileBtn?.getAttribute('aria-expanded') === 'true';
        if (isOpen) closeMobileMenu();
        else openMobileMenu();
      }

      document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle
        const mobileBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileBtn) {
          mobileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleMobileMenu();
          });
        }

        // Close button inside mobile menu
        const mobileCloseBtn = document.getElementById('mobile-menu-close');
        if (mobileCloseBtn) {
          mobileCloseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            closeMobileMenu();
          });
        }

        // Close mobile menu on window resize (if switching to desktop)
        window.addEventListener('resize', function() {
          if (window.innerWidth >= 1024) {
            closeMobileMenu();
          }
        });

        // Close mobile menu on Escape
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            closeMobileMenu();
          }
        });

        // Desktop dropdown menus
        document.querySelectorAll('.dropdown-wrapper').forEach(function(wrapper) {
          const btn = wrapper.querySelector('.dropdown-btn');
          const menu = wrapper.querySelector('.dropdown-menu');
          let timeout;

          if (!btn || !menu) return;

          // Hover behaviour for desktop (mouse)
          wrapper.addEventListener('mouseenter', function() {
            clearTimeout(timeout);
            openMenu(menu, btn);
          });
          wrapper.addEventListener('mouseleave', function() {
            timeout = setTimeout(function() { closeMenu(menu, btn); }, 120);
          });

          // Click/tap toggle for touch devices & keyboard users
          btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            if (isOpen) closeMenu(menu, btn);
            else openMenu(menu, btn);
          });

          // close on Escape when menu is open
          wrapper.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeMenu(menu, btn);
          });
        });

        // Close open menus when clicking outside
        document.addEventListener('click', function(e) {
          document.querySelectorAll('.dropdown-wrapper .dropdown-menu').forEach(function(menu) {
            const wrapper = menu.closest('.dropdown-wrapper');
            const btn = wrapper?.querySelector('.dropdown-btn');
            if (!wrapper.contains(e.target)) closeMenu(menu, btn);
          });
        });

        // Close dropdowns on Escape anywhere
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape') {
            document.querySelectorAll('.dropdown-wrapper .dropdown-menu').forEach(function(menu) {
              const wrapper = menu.closest('.dropdown-wrapper');
              const btn = wrapper?.querySelector('.dropdown-btn');
              closeMenu(menu, btn);
            });
          }
        });
      });
    })();
  </script>
