<?php
include_once 'session.php';

// --- Gestion des articles ---
// Liste des fichiers d'articles à parcourir
$article_files = [
    'articles_index.txt',
    'articles_apres-match.txt',
    'articles_interview.txt',
    'articles_accreditation.txt',
    'articles_autre.txt'
];
$upload_dir = 'uploads/';
$articles = [];

// Charger tous les articles de tous les fichiers
foreach ($article_files as $file) {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line, 6);
            $articles[] = [
                'title' => $parts[0] ?? '',
                // Décoder le contenu base64 si présent
                'content' => isset($parts[1]) ? base64_decode($parts[1]) : '',
                'image' => $parts[2] ?? '',
                'date' => !empty($parts[3]) ? $parts[3] : null, // Ensure date is null if empty
                'source_file' => $file
            ];
        }
    }
}
// Trier tous les articles par date décroissante (plus récent d'abord)
usort($articles, function($a, $b) {
    return strtotime($b['date']) <=> strtotime($a['date']);
});
// Garder seulement les 3 plus récents
$articles = array_slice($articles, 0, 3);

// Préparer l'édition
$edit_idx = null;
$edit_title = '';
$edit_content = '';
$edit_image = '';
if (is_logged_in() && isset($_GET['edit'])) {
    // Désactiver la préparation de l'édition sur la page d'accueil
    // Rien à faire ici
}

function get_article_page($source_file) {
    if (strpos($source_file, 'apres-match') !== false) return 'apres-match.php';
    if (strpos($source_file, 'interview') !== false) return 'interview.php';
    if (strpos($source_file, 'accreditation') !== false) return 'accreditation.php';
    if (strpos($source_file, 'autre') !== false) return 'autre.php';
    return 'index.php';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Articles Sportifs</title>
  <link rel="stylesheet" href="style.php">
  <style>
    @media (max-width: 900px) {
      .home-flex-articles {
        flex-direction: column !important;
        gap: 30px !important;
        width: 100vw !important;
        margin-left: 0 !important;
      }
      .home-main-article, .home-side-articles {
        margin-left: 0 !important;
        max-width: 98vw !important;
      }
    }
    @media (max-width: 700px) {
      .home-main-article, .home-side-articles {
        max-width: 100vw !important;
        padding: 0 !important;
      }
      .article img {
        max-width: 100vw !important;
        height: auto !important;
      }
      .presentation-section {
        margin-left: 0 !important;
        padding: 0 2vw !important;
      }
      .presentation-section > * {
        margin-left: 0 !important;
      }
    }
  </style>
</head>
<body class="<?php echo (isset($_COOKIE['darkMode']) && $_COOKIE['darkMode'] === '1') ? 'dark-mode' : ''; ?>">
  <?php include 'header.php'; ?>
  <div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
      <!-- Bloc de présentation -->
      <section class="presentation-section" style="margin-bottom:32px;max-width:800px;margin-left:auto;margin-right:auto;text-align:left;">
        <h2 style="font-size:2em;margin-bottom:12px;margin-left:-200px;">Présentation</h2>
        <div style="font-size:1.1em;line-height:1.7;margin-left:-200px;">Bonjour à tous </div>
        <div style="height: 20px;"></div>
        <div style="font-size:1.1em;line-height:1.7;margin-left:-200px;">Ce blog a été créé par deux étudiants se passionnant pour l’informatique, l’écriture journalistique et le sport. Ce site est avant tout un prolongement du travail que vous pouvez retrouver sur mes différents comptes Twitter, ainsi que sur le média « NationalFootball » et sur la filiale « NF Bourgogne-Franche-Comté ».</div>
        <div style="height: 20px;"></div>
        <div style="font-size:1.1em;line-height:1.7;margin-left:-200px;">Ce site va me permettre de m’exprimer davantage sur ma région natale, grâce à des interviews avec des acteurs du milieu footballistique régional. Mais également grâce à l’obtention d’accréditations, aux publications d’articles, mais aussi des suivis des différents clubs du niveau national et régional.</div>
        <div style="height: 20px;"></div>
        <div style="font-size:1.1em;line-height:1.7;margin-left:-200px;">J’aimerai dans les années à venir devenir journaliste sportif et ce rêve passe par ce blog et par vos différents retours sur mes différents réseaux sociaux (notamment Twitter).</div>
        <div style="height: 20px;"></div>
        <div style="font-size:1.1em;line-height:1.7;margin-left:-200px;">Bonne lecture à tous !</div>
      </section>
      <!-- Gestion des articles -->
      <section class="other-articles" style="display:block;margin-bottom:20px;">
        <h2 style="font-size:2em;margin-left:-200px;">Dernières parutions</h2>
        <?php if (count($articles) > 0): ?>
          <?php
            $main_article = $articles[0];
            $small_articles = array_slice($articles, 1, 2);
          ?>
          <div class="home-flex-articles" style="display:flex;justify-content:flex-start;align-items:flex-start;gap:80px;width:130vw;margin-left:calc(-50vw + 50%);flex-wrap:wrap;">
            <!-- Article principal à gauche -->
            <div class="home-main-article" style="flex:0 0 800px;max-width:800px;width:100%;margin-left:100px;">
              <?php
                $main_link = get_article_page($main_article['source_file']) . '?show=' . urlencode($main_article['date']);
              ?>
              <a href="<?php echo $main_link; ?>" style="text-decoration:none;color:inherit;">
                <div class="article toggleable" style="background:#fff;color:#222;padding:32px 32px 24px 32px;margin-bottom:36px;box-shadow:0 4px 16px rgba(0,0,0,0.13);border-radius:14px;text-align:left;cursor:pointer;width:100%;margin-left:0;">
                  <?php if (!empty($main_article['image']) && file_exists($upload_dir . $main_article['image'])): ?>
                    <img src="<?php echo $upload_dir . htmlspecialchars($main_article['image']); ?>" alt="Photo" style="width:100%;max-width:700px;max-height:420px;display:block;margin-bottom:22px;border-radius:10px;filter: brightness(1) contrast(1);">
                  <?php endif; ?>
                  <h3 style="font-size:2.1em;margin-bottom:10px;"><?php echo htmlspecialchars($main_article['title']); ?></h3>
                  <?php if (!empty($main_article['date'])): ?>
                    <div style="font-size:1.1em;margin-bottom:0;">Publié le <?php echo date('d/m/Y H:i', strtotime($main_article['date'])); ?></div>
                  <?php else: ?>
                    <div style="font-size:1.1em;margin-bottom:0;">Date inconnue</div>
                  <?php endif; ?>
                  <div class="article-content" style="display:none;">
                    <?php echo htmlspecialchars($main_article['content']); ?>
                  </div>
                </div>
              </a>
            </div>
            <!-- Les deux petits articles à droite, en colonne -->
            <?php if (count($small_articles) > 0): ?>
              <div class="home-side-articles" style="display:flex;flex-direction:column;gap:30px;max-width:340px;width:100%;margin-left:40px;margin-top:39px;">
                <?php foreach ($small_articles as $i => $a): ?>
                  <?php
                    $alink = get_article_page($a['source_file']) . '?show=' . urlencode($a['date']);
                  ?>
                  <a href="<?php echo $alink; ?>" style="text-decoration:none;color:inherit;">
                    <div class="article toggleable" style="background:#fff;color:#222;padding:18px 18px 12px 18px;box-shadow:0 2px 8px rgba(0,0,0,0.10);border-radius:10px;max-width:320px;width:100%;text-align:left;cursor:pointer;min-height:220px;margin-bottom:0;">
                      <?php if (!empty($a['image']) && file_exists($upload_dir . htmlspecialchars($a['image']))): ?>
                        <img src="<?php echo $upload_dir . htmlspecialchars($a['image']); ?>" alt="Photo" style="width:100%;max-width:280px;max-height:160px;display:block;margin-bottom:12px;border-radius:7px;filter: brightness(1) contrast(1);">
                      <?php endif; ?>
                      <h4 style="font-size:1.25em;margin-bottom:8px;"><?php echo htmlspecialchars($a['title']); ?></h4>
                      <?php if (!empty($a['date'])): ?>
                        <div style="font-size:0.98em;">Publié le <?php echo date('d/m/Y H:i', strtotime($a['date'])); ?></div>
                      <?php else: ?>
                        <div style="font-size:0.98em;">Date inconnue</div>
                      <?php endif; ?>
                      <div class="article-content" style="display:none;">
                        <?php echo htmlspecialchars($a['content']); ?>
                      </div>
                    </div>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </section>
      <footer class="social">
      </footer>
    </main>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('article').forEach(function(article) {
      article.addEventListener('click', function(event) {
        var content = article.querySelector('.article-content');
        if (content) {
          content.style.display = content.style.display === 'block' ? 'none' : 'block';
        }
      });
    });
  });
  </script>
</body>
</html>