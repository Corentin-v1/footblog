<?php include_once 'session.php'; ?>
<?php
$articles_file = 'articles_apres-match.txt';
$upload_dir = 'uploads/';
$articles = [];

// Charger les articles (4 champs : titre|contenu|image|date)
if (file_exists($articles_file)) {
    $lines = file($articles_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line, 4);
        $articles[] = [
            'title' => $parts[0] ?? '',
            'content' => isset($parts[1]) ? base64_decode($parts[1]) : '',
            'image' => $parts[2] ?? '',
            'date' => $parts[3] ?? ''
        ];
    }
    // Trier par date décroissante (plus récent en haut)
    usort($articles, function($a, $b) {
        return strtotime($b['date']) <=> strtotime($a['date']);
    });
}

// Ajouter un article (ajout automatique de la date)
if (is_logged_in() && isset($_POST['add_article'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_name = '';
    if ($title && $content) {
        if (!empty($_FILES['photo']['name'])) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg','image/png','image/gif','image/webp'];
            if (in_array($ext, $allowed) && in_array($mime, $allowed_mimes)) {
                $image_name = uniqid('img_') . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $image_name);
            }
        }
        $date = date('Y-m-d H:i:s');
        file_put_contents($articles_file, $title . '|' . base64_encode($content) . '|' . $image_name . '|' . $date . PHP_EOL, FILE_APPEND);
        header('Location: apres-match.php');
        exit;
    }
}

// Supprimer un article
if (is_logged_in() && isset($_POST['delete_article'])) {
    $idx = intval($_POST['delete_article']);
    if (isset($articles[$idx])) {
        if (!empty($articles[$idx]['image']) && file_exists($upload_dir . $articles[$idx]['image'])) {
            unlink($upload_dir . $articles[$idx]['image']);
        }
        unset($articles[$idx]);
        $lines = [];
        foreach ($articles as $a) {
            $lines[] = $a['title'] . '|' . $a['content'] . '|' . ($a['image'] ?? '') . '|' . ($a['date'] ?? '');
        }
        file_put_contents($articles_file, implode(PHP_EOL, $lines) . PHP_EOL);
        header('Location: apres-match.php');
        exit;
    }
}

// Modifier un article (préserver la date)
if (is_logged_in() && isset($_POST['edit_article'])) {
    $idx = intval($_POST['edit_article']);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_name = $articles[$idx]['image'] ?? '';
    $date = $articles[$idx]['date'] ?? date('Y-m-d H:i:s');
    if (isset($articles[$idx]) && $title && $content) {
        if (!empty($_FILES['photo']['name'])) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg','image/png','image/gif','image/webp'];
            if (in_array($ext, $allowed) && in_array($mime, $allowed_mimes)) {
                if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                $image_name = uniqid('img_') . '.' . $ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $image_name);
            }
        }
        $articles[$idx] = ['title' => $title, 'content' => base64_encode($content), 'image' => $image_name, 'date' => $date];
        $lines = [];
        foreach ($articles as $a) {
            $lines[] = $a['title'] . '|' . $a['content'] . '|' . ($a['image'] ?? '') . '|' . ($a['date'] ?? '');
        }
        file_put_contents($articles_file, implode(PHP_EOL, $lines) . PHP_EOL);
        header('Location: apres-match.php');
        exit;
    }
}

// Préparer l'édition
$edit_idx = null;
$edit_title = '';
$edit_content = '';
$edit_image = '';
if (is_logged_in() && isset($_GET['edit']) && isset($articles[intval($_GET['edit'])])) {
    $edit_idx = intval($_GET['edit']);
    $edit_title = $articles[$edit_idx]['title'];
    $edit_content = $articles[$edit_idx]['content'];
    $edit_image = $articles[$edit_idx]['image'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Article d'après match</title>
  <link rel="stylesheet" href="style.php">
</head>
<body>
  <?php include 'header.php'; ?>
  <div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
      <section class="other-articles" style="display:block;margin-bottom:20px;">
        <h2 style="font-size:2.2em;">Article(s) d'après match</h2>
        <?php if (is_logged_in()): ?>
          <div style="margin-bottom:20px;">
            <form method="post" enctype="multipart/form-data">
              <input type="text" name="title" placeholder="Titre" required style="width:100%;padding:8px;margin-bottom:8px;" value="<?php echo htmlspecialchars($edit_title); ?>">
              <textarea name="content" placeholder="Contenu" required style="width:100%;padding:8px;margin-bottom:8px;"><?php echo htmlspecialchars($edit_content); ?></textarea>
              <input type="file" name="photo" accept="image/*" style="margin-bottom:8px;">
              <?php if ($edit_idx !== null && !empty($edit_image)): ?>
                <div style="margin-bottom:8px;">
                  <img src="<?php echo $upload_dir . htmlspecialchars($edit_image); ?>" alt="Aperçu" style="max-width:120px;max-height:80px;">
                </div>
              <?php endif; ?>
              <?php if ($edit_idx !== null): ?>
                <input type="hidden" name="edit_article" value="<?php echo $edit_idx; ?>">
                <button type="submit" style="padding:8px 16px;">Modifier</button>
                <a href="apres-match.php" style="margin-left:10px;">Annuler</a>
              <?php else: ?>
                <button type="submit" name="add_article" style="padding:8px 16px;">Ajouter</button>
              <?php endif; ?>
            </form>
          </div>
        <?php endif; ?>
        <?php foreach ($articles as $i => $a): ?>
          <?php
            $show_content = (isset($_GET['show']) && $_GET['show'] === $a['date']);
            $article_id = 'article_' . md5($a['date']);
          ?>
          <article id="<?php echo $article_id; ?>" class="toggle-article" onclick="toggleContent(this)" style="background:#fff;padding:30px;margin-bottom:30px;box-shadow:0 4px 12px rgba(0,0,0,0.13);border-radius:10px;cursor:pointer;">
            <h3><?php echo htmlspecialchars($a['title']); ?></h3>
            <?php if (!empty($a['image']) && file_exists($upload_dir . $a['image'])): ?>
              <img src="<?php echo $upload_dir . htmlspecialchars($a['image']); ?>" alt="Photo" style="max-width:600px;max-height:400px;display:block;margin-bottom:10px;">
            <?php endif; ?>
            <small style="color:#888;display:block;margin-bottom:10px;">Publié le : <?php echo htmlspecialchars($a['date']); ?></small>
            <div class="article-content" style="display:<?php echo $show_content ? 'block' : 'none'; ?>;">
              <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    </main>
  </div>
</body>
</html>
<script>
function toggleContent(article) {
  var content = article.querySelector('.article-content');
  if (content) {
    content.style.display = (content.style.display === 'none' || content.style.display === '') ? 'block' : 'none';
  }
}
// Ajout : scroll automatique sur l'article si ?show= est présent
<?php if (isset($_GET['show'])): ?>
window.addEventListener('DOMContentLoaded', function() {
  var el = document.getElementById('article_<?php echo md5($_GET['show']); ?>');
  if (el) {
    el.scrollIntoView({behavior: 'smooth', block: 'start'});
    // Ouvre le contenu si ce n'est pas déjà visible
    var content = el.querySelector('.article-content');
    if (content && content.style.display !== 'block') {
      content.style.display = 'block';
    }
  }
});
<?php endif; ?>
</script>