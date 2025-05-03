<?php include_once 'session.php'; ?>
<?php
$articles_file = 'articles_interview.txt';
$upload_dir = 'uploads/';
$articles = [];
// Charger les articles (4 champs : titre|contenu|image|date)
if (file_exists($articles_file)) {
    $lines = file($articles_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line, 4);
        $articles[] = [
            'title' => $parts[0] ?? '',
            'content' => $parts[1] ?? '',
            'image' => $parts[2] ?? '',
            'date' => $parts[3] ?? ''
        ];
    }
    // Trier par date décroissante (plus récent en haut)
    usort($articles, function($a, $b) {
        return strtotime($b['date']) <=> strtotime($a['date']);
    });
}
if (is_logged_in() && isset($_POST['add_article'])) {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $image_name = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);
        $allowed_mimes = ['image/jpeg','image/png','image/gif','image/webp'];
        if (in_array($ext, $allowed) && in_array($mime, $allowed_mimes)) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $image_name = uniqid('img_') . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
        }
    }
    $date = date('Y-m-d H:i:s');
    file_put_contents($articles_file, $title . '|' . $content . '|' . $image_name . '|' . $date . PHP_EOL, FILE_APPEND);
    header('Location: interview.php');
    exit;
}
if (is_logged_in() && isset($_GET['delete']) && isset($articles[intval($_GET['delete'])])) {
    unset($articles[intval($_GET['delete'])]);
    file_put_contents($articles_file, implode(PHP_EOL, array_map(function ($a) {
        return $a['title'] . '|' . $a['content'] . '|' . $a['image'] . '|' . ($a['date'] ?? '');
    }, $articles)) . PHP_EOL);
    header('Location: interview.php');
    exit;
}
if (is_logged_in() && isset($_GET['edit']) && isset($articles[intval($_GET['edit'])])) {
    $edit_article = $articles[intval($_GET['edit'])];
}
if (is_logged_in() && isset($_POST['edit_article'])) {
    $idx = intval($_POST['edit_article']);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_name = $articles[$idx]['image'] ?? '';
    $date = date('Y-m-d H:i:s'); // Update the date to the current date and time
    if (isset($articles[$idx]) && $title && $content) {
        if (!empty($_FILES['image']['name'])) {
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','gif','webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);
            $allowed_mimes = ['image/jpeg','image/png','image/gif','image/webp'];
            if (in_array($ext, $allowed) && in_array($mime, $allowed_mimes)) {
                if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                    unlink($upload_dir . $image_name);
                }
                $image_name = uniqid('img_') . '.' . $ext;
                move_uploaded_file($_FILES['image']['tmp_name'], $upload_dir . $image_name);
            }
        }
        $articles[$idx] = [
            'title' => $title,
            'content' => $content,
            'image' => $image_name,
            'date' => $date // Update the date
        ];
        $lines = [];
        foreach ($articles as $a) {
            $lines[] = $a['title'] . '|' . $a['content'] . '|' . ($a['image'] ?? '') . '|' . ($a['date'] ?? '');
        }
        file_put_contents($articles_file, implode(PHP_EOL, $lines) . PHP_EOL);
        header('Location: interview.php');
        exit;
    }
}

function getArticleCounts($article_id) {
    $file = "counts/{$article_id}.json";
    if (file_exists($file)) {
        return json_decode(file_get_contents($file), true);
    }
    return ['likes' => 0, 'dislikes' => 0];
}

function saveArticleCounts($article_id, $counts) {
    $file = "counts/{$article_id}.json";
    if (!is_dir('counts')) {
        mkdir('counts', 0777, true);
    }
    file_put_contents($file, json_encode($counts));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Interview</title>
  <link rel="stylesheet" href="style.php">
</head>
<body>
  <?php include 'header.php'; ?>
  <div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
      <section class="other-articles" style="display:block;margin-bottom:20px;">
        <h2 style="font-size:2.2em;">Interview(s)</h2>
        <?php if (is_logged_in()): ?>
          <!-- Formulaire CRUD identique à apres-match.php -->
          <form action="interview.php" method="post" enctype="multipart/form-data">
            <input type="text" name="title" placeholder="Titre" required>
            <textarea name="content" placeholder="Contenu" required></textarea>
            <input type="file" name="image" accept="image/*">
            <button type="submit" name="add_article">Ajouter l'article</button>
          </form>
        <?php endif; ?>
        <?php foreach ($articles as $i => $a): ?>
          <?php
            $article_id = 'article_' . md5($a['date']);
            $counts = getArticleCounts($article_id);
          ?>
          <article id="<?php echo $article_id; ?>" class="toggle-article">
            <?php if (is_logged_in()): ?>
              <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="edit_article" value="<?php echo $i; ?>">
                <input type="text" name="title" value="<?php echo htmlspecialchars($a['title']); ?>" required style="width:100%;padding:8px;margin-bottom:8px;">
                <?php if (!empty($a['image']) && file_exists($upload_dir . $a['image'])): ?>
                  <img src="<?php echo $upload_dir . htmlspecialchars($a['image']); ?>" alt="Photo" style="max-width:600px;max-height:500px;margin-bottom:8px;">
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" style="margin-bottom:8px;">
                <textarea name="content" required style="width:100%;padding:8px;margin-bottom:8px;"><?php echo htmlspecialchars($a['content']); ?></textarea>
                <button type="submit" style="padding:8px 16px;">Enregistrer</button>
              </form>
            <?php else: ?>
              <h3><?php echo htmlspecialchars($a['title']); ?></h3>
              <?php if (!empty($a['image']) && file_exists($upload_dir . $a['image'])): ?>
                <img src="<?php echo $upload_dir . htmlspecialchars($a['image']); ?>" alt="Photo">
              <?php endif; ?>
              <small>Publié le : <?php echo htmlspecialchars($a['date']); ?></small>
              <div class="article-content">
                <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
              </div>
            <?php endif; ?>
            <div class="like-dislike-container">
              <button class="like-btn" onclick="handleLike('<?php echo $article_id; ?>', event)">👍</button>
              <span id="like-count-<?php echo $article_id; ?>"><?php echo $counts['likes']; ?></span>
              <button class="dislike-btn" onclick="handleDislike('<?php echo $article_id; ?>', event)">👎</button>
              <span id="dislike-count-<?php echo $article_id; ?>"><?php echo $counts['dislikes']; ?></span>
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

async function updateCounts(articleId, action) {
  const response = await fetch('update_counts.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ articleId, action })
  });
  const data = await response.json();
  if (data.success) {
    document.getElementById(`like-count-${articleId}`).textContent = data.likes;
    document.getElementById(`dislike-count-${articleId}`).textContent = data.dislikes;
  }
}

function handleLike(articleId, event) {
  event.stopPropagation();
  updateCounts(articleId, 'like');
}

function handleDislike(articleId, event) {
  event.stopPropagation();
  updateCounts(articleId, 'dislike');
}

function toggleContent(article) {
  var content = article.querySelector('.article-content');
  if (content) {
    content.style.display = (content.style.display === 'none' || content.style.display === '') ? 'block' : 'none';
  }
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('article').forEach(function(article) {
        article.addEventListener('click', function() {
            var content = article.querySelector('.article-content');
            if (content) {
                content.style.display = content.style.display === 'block' ? 'none' : 'block';
            }
        });
    });
});
</script>