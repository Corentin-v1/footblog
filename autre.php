<?php include_once 'session.php'; ?>
<?php
$articles_file = 'articles_autre.txt';
$upload_dir = 'uploads/';
$articles = [];

// Charger les articles (6 champs : titre|contenu|image|date|likes|dislikes)
if (file_exists($articles_file)) {
    $lines = file($articles_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line, 6);
        $articles[] = [
            'title' => $parts[0] ?? '',
            'content' => $parts[1] ?? '', // Suppression du décodage Base64
            'image' => $parts[2] ?? '',
            'date' => $parts[3] ?? '',
            'likes' => $parts[4] ?? 0,
            'dislikes' => $parts[5] ?? 0
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
        $likes = 0;
        $dislikes = 0;
        $date = date('Y-m-d H:i:s');
        file_put_contents($articles_file, $title . '|' . $content . '|' . $image_name . '|' . $date . '|' . $likes . '|' . $dislikes . PHP_EOL, FILE_APPEND); // Suppression de l'encodage Base64
        header('Location: autre.php');
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
            $lines[] = $a['title'] . '|' . $a['content'] . '|' . ($a['image'] ?? '') . '|' . ($a['date'] ?? '') . '|' . ($a['likes'] ?? 0) . '|' . ($a['dislikes'] ?? 0);
        }
        file_put_contents($articles_file, implode(PHP_EOL, $lines) . PHP_EOL);
        header('Location: autre.php');
        exit;
    }
}

// Modifier un article (préserver la date)
if (is_logged_in() && isset($_POST['edit_article'])) {
    $idx = intval($_POST['edit_article']);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_name = $articles[$idx]['image'] ?? '';
    $date = date('Y-m-d H:i:s'); // Update the date to the current date and time
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
        $likes = isset($articles[$idx]['likes']) ? $articles[$idx]['likes'] : 0;
        $dislikes = isset($articles[$idx]['dislikes']) ? $articles[$idx]['dislikes'] : 0;
        $articles[$idx] = ['title' => $title, 'content' => $content, 'image' => $image_name, 'date' => $date, 'likes' => $likes, 'dislikes' => $dislikes]; // Update the date
        $lines = [];
        foreach ($articles as $a) {
            $lines[] = $a['title'] . '|' . $a['content'] . '|' . ($a['image'] ?? '') . '|' . ($a['date'] ?? '') . '|' . ($a['likes'] ?? 0) . '|' . ($a['dislikes'] ?? 0);
        }
        file_put_contents($articles_file, implode(PHP_EOL, $lines) . PHP_EOL);
        header('Location: autre.php');
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

function getArticleCounts($article_id) {
    $file = "counts/{$article_id}.json";
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return ['likes' => 0, 'dislikes' => 0];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Autre article</title>
  <link rel="stylesheet" href="style.php">
</head>
<body>
  <?php include 'header.php'; ?>

  <div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
      <section class="other-articles" style="display:block;margin-bottom:20px;">
        <h2 style="font-size:2.2em;">Autre(s) article(s)</h2>
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
                <a href="autre.php" style="margin-left:10px;">Annuler</a>
              <?php else: ?>
                <button type="submit" name="add_article" style="padding:8px 16px;">Ajouter</button>
              <?php endif; ?>
            </form>
          </div>
        <?php endif; ?>
        <?php foreach ($articles as $i => $a): ?>
          <?php
            $article_id = md5($a['date']);
            $likes = isset($a['likes']) ? $a['likes'] : 0;
            $dislikes = isset($a['dislikes']) ? $a['dislikes'] : 0;
          ?>
          <article id="article_<?php echo $article_id; ?>" class="toggle-article">
            <?php if (is_logged_in()): ?>
              <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="edit_article" value="<?php echo $i; ?>">
                <input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>" required style="width:100%;padding:8px;margin-bottom:8px;">
                <?php if (!empty($a['image']) && file_exists($upload_dir . $a['image'])): ?>
                  <img src="<?php echo $upload_dir . htmlspecialchars($a['image']); ?>" alt="Photo" style="max-width:600px;max-height:500px;margin-bottom:8px;">
                <?php endif; ?>
                <input type="file" name="photo" accept="image/*" style="margin-bottom:8px;">
                <textarea name="content" required style="width:100%;padding:8px;margin-bottom:8px;"><?php echo htmlspecialchars($a['content']); ?></textarea> 
                <button type="submit" style="padding:8px 16px;">Enregistrer</button>
              </form>
            <?php else: ?>
              <h3><?php echo htmlspecialchars($a['title']); ?></h3>
              <?php if (!empty($a['image']) && file_exists($upload_dir . $a['image'])): ?>
                <img src="<?php echo $upload_dir . htmlspecialchars($a['image']); ?>" alt="Photo">
              <?php endif; ?>
              <small>Publié le : <?php echo date('d/m/Y H:i', strtotime($a['date'])); ?></small>
              <div class="article-content">
                <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p> 
              </div>
            <?php endif; ?>
            <div class="like-dislike-container">
              <button class="like-btn" onclick="handleLike('<?php echo $article_id; ?>', event)">👍</button>
              <span id="like-count-<?php echo $article_id; ?>"><?php echo $likes; ?></span>
              <button class="dislike-btn" onclick="handleDislike('<?php echo $article_id; ?>', event)">👎</button>
              <span id="dislike-count-<?php echo $article_id; ?>"><?php echo $dislikes; ?></span>
            </div>
          </article>
        <?php endforeach; ?>
      </section>
    </main>
  </div>
</body>
</html>
<script>
const userVotes = {};

function toggleContent(article) {
  var content = article.querySelector('.article-content');
  if (content) {
    content.style.display = (content.style.display === 'none' || content.style.display === '') ? 'block' : 'none';
  }
}

async function updateCounts(articleId, action) {
  const previousAction = userVotes[articleId] || null;
  const response = await fetch('update_counts.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ articleId, action, previousAction })
  });
  const data = await response.json();
  if (data.success) {
    document.getElementById(`like-count-${articleId}`).textContent = data.likes;
    document.getElementById(`dislike-count-${articleId}`).textContent = data.dislikes;
    userVotes[articleId] = action; // Update the user's vote
  }
}

function handleLike(articleId, event) {
  event.stopPropagation();
  const currentVote = userVotes[articleId];
  if (currentVote === 'like') return; // Prevent duplicate likes
  updateCounts(articleId, 'like');
}

function handleDislike(articleId, event) {
  event.stopPropagation();
  const currentVote = userVotes[articleId];
  if (currentVote === 'dislike') return; // Prevent duplicate dislikes
  updateCounts(articleId, 'dislike');
}

document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('article').forEach(function(article) {
    article.addEventListener('click', function(event) {
      if (event.target.classList.contains('like-btn') || event.target.classList.contains('dislike-btn')) {
        return;
      }
      toggleContent(article);
    });
  });
});

// Ajout : scroll automatique sur l'article si ?show= est présent
<?php if (isset($_GET['show'])): ?>
window.addEventListener('DOMContentLoaded', function() {
  var el = document.getElementById('article_<?php echo md5($_GET['show']); ?>');
  if (el) {
    el.scrollIntoView({behavior: 'smooth', block: 'start'});
    var content = el.querySelector('.article-content');
    if (content && content.style.display !== 'block') {
      content.style.display = 'block';
    }
  }
});
<?php endif; ?>
</script>