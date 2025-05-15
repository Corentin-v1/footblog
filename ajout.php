<?php
include_once 'session.php';
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

// Retirer la page "index" des pages disponibles pour l'ajout
$pages = [
    'apres-match' => "Article d'après match",
    'interview' => "Interview",
    'accreditation' => "Accréditation",
    'autre' => "Autre article"
];

$upload_dir = 'uploads/';
$msg = '';

// Détermination de la page sélectionnée pour affichage
$selected_page = $_GET['view_page'] ?? 'index';
if ($selected_page === 'all') {
    $view_pages = array_keys($pages);
} else {
    $view_pages = [$selected_page];
}

// Ajout d'un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_article'])) {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $page = $_POST['page'] ?? '';
    $image_name = '';

    if ($title && $content && isset($pages[$page])) {
        // Gestion upload image
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
        $file = "articles_$page.txt";
        $date = date('Y-m-d H:i:s');
        file_put_contents($file, $title . '|' . base64_encode($content) . '|' . $image_name . '|' . $date . PHP_EOL, FILE_APPEND);
        $msg = "Article ajouté avec succès dans la page « {$pages[$page]} ».";
    } else {
        $msg = "Veuillez remplir tous les champs et choisir une page.";
    }
}

// Suppression d'un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article'])) {
    $del_page = $_POST['del_page'];
    $del_idx = intval($_POST['delete_article']);
    $file = "articles_$del_page.txt";
    $articles = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line, 4);
            $articles[] = [
                'title' => $parts[0] ?? '',
                'content' => isset($parts[1]) ? base64_decode($parts[1]) : '',
                'image' => $parts[2] ?? '',
                'date' => $parts[3] ?? ''
            ];
        }
        if (isset($articles[$del_idx])) {
            // Supprimer l'image associée
            if (!empty($articles[$del_idx]['image']) && file_exists($upload_dir . $articles[$del_idx]['image'])) {
                unlink($upload_dir . $articles[$del_idx]['image']);
            }
            unset($articles[$del_idx]);
            $lines = [];
            foreach ($articles as $a) {
                $lines[] = $a['title'] . '|' . $a['content'] . '|' . ($a['image'] ?? '') . '|' . ($a['date'] ?? '');
            }
            file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);
            $msg = "Article supprimé.";
        }
    }
}

// Préparer l'édition d'un article
$edit_idx = null;
$edit_page = null;
$edit_title = '';
$edit_content = '';
$edit_image = '';
$edit_date = '';
if (isset($_GET['edit_page']) && isset($_GET['edit_idx'])) {
    $edit_page = $_GET['edit_page'];
    $edit_idx = intval($_GET['edit_idx']);
    $file = "articles_$edit_page.txt";
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (isset($lines[$edit_idx])) {
            $parts = explode('|', $lines[$edit_idx], 4);
            $edit_title = $parts[0] ?? '';
            $edit_content = isset($parts[1]) ? base64_decode($parts[1]) : '';
            $edit_image = $parts[2] ?? '';
            $edit_date = $parts[3] ?? '';
        }
    }
}

// Modification d'un article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_article'])) {
    $edit_page = $_POST['edit_page'];
    $edit_idx = intval($_POST['edit_idx']);
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $image_name = $_POST['old_image'] ?? '';
    $file = "articles_$edit_page.txt";
    $articles = [];
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $parts = explode('|', $line, 4);
            $articles[] = [
                'title' => $parts[0] ?? '',
                'content' => isset($parts[1]) ? base64_decode($parts[1]) : '',
                'image' => $parts[2] ?? '',
                'date' => $parts[3] ?? ''
            ];
        }
        if (isset($articles[$edit_idx]) && $title && $content) {
            // Gestion upload image (remplacement)
            if (!empty($_FILES['photo']['name'])) {
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
                $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $_FILES['photo']['tmp_name']);
                finfo_close($finfo);
                $allowed_mimes = ['image/jpeg','image/png','image/gif','image/webp'];
                if (in_array($ext, $allowed) && in_array($mime, $allowed_mimes)) {
                    // Supprimer l'ancienne image si elle existe
                    if (!empty($image_name) && file_exists($upload_dir . $image_name)) {
                        unlink($upload_dir . $image_name);
                    }
                    $image_name = uniqid('img_') . '.' . $ext;
                    move_uploaded_file($_FILES['photo']['tmp_name'], $upload_dir . $image_name);
                }
            }
            $date = $articles[$edit_idx]['date'] ?? date('Y-m-d H:i:s');
            $articles[$edit_idx] = ['title' => $title, 'content' => base64_encode($content), 'image' => $image_name, 'date' => $date];
            $lines = [];
            foreach ($articles as $a) {
                $lines[] = $a['title'] . '|' . (is_string($a['content']) && base64_decode($a['content'], true) !== false ? $a['content'] : base64_encode($a['content'])) . '|' . ($a['image'] ?? '') . '|' . ($a['date'] ?? '');
            }
            file_put_contents($file, implode(PHP_EOL, $lines) . PHP_EOL);
            $msg = "Article modifié.";
            header("Location: ajout.php?view_page=$edit_page");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un article</title>
    <link rel="stylesheet" href="style.php">
    <style>
    @media (max-width: 700px) {
        .form-container {
            padding: 8px !important;
            max-width: 100vw !important;
        }
        .ajout-article {
            padding: 8px !important;
            font-size: 0.97em !important;
        }
        .ajout-article img {
            max-width: 100vw !important;
            height: auto !important;
        }
    }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['dark_mode']) && $_COOKIE['dark_mode'] === '1' ? 'dark-mode' : ''; ?>">
<?php include 'header.php'; ?>
<div class="container">
    <?php include 'sidebar.php'; ?>
    <main class="content">
        <div class="form-container" style="max-width:700px;margin:40px auto;padding:30px;background:#fff;box-shadow:0 2px 8px rgba(0,0,0,0.1);border-radius:8px;">
            <h2>Ajouter un article</h2>
            <?php if ($msg): ?>
                <div class="<?php echo strpos($msg, 'succès') !== false || strpos($msg, 'modifié') !== false ? 'msg-success' : 'msg-error'; ?>" style="margin-bottom:15px;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>
            <?php if ($edit_idx !== null && $edit_page !== null): ?>
                <!-- Formulaire édition -->
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="edit_article" value="1">
                    <input type="hidden" name="edit_page" value="<?php echo htmlspecialchars($edit_page); ?>">
                    <input type="hidden" name="edit_idx" value="<?php echo $edit_idx; ?>">
                    <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($edit_image); ?>">
                    <label>Titre<br>
                        <input type="text" name="title" required style="width:100%;padding:8px;margin-bottom:10px;" value="<?php echo htmlspecialchars($edit_title); ?>">
                    </label><br>
                    <label>Contenu<br>
                        <textarea name="content" required style="width:100%;padding:8px;margin-bottom:10px;"><?php echo htmlspecialchars($edit_content); ?></textarea>
                    </label><br>
                    <label>Photo<br>
                        <input type="file" name="photo" accept="image/*" style="margin-bottom:10px;">
                        <?php if (!empty($edit_image)): ?>
                            <div style="margin-bottom:8px;">
                                <img src="<?php echo $upload_dir . htmlspecialchars($edit_image); ?>" alt="Aperçu" style="max-width:120px;max-height:80px;">
                            </div>
                        <?php endif; ?>
                    </label><br>
                    <button type="submit" style="padding:8px 16px;">Modifier</button>
                    <a href="ajout.php?view_page=<?php echo htmlspecialchars($edit_page); ?>" style="margin-left:10px;">Annuler</a>
                </form>
            <?php else: ?>
                <!-- Formulaire ajout -->
                <form method="post" enctype="multipart/form-data" class="form-container">
                    <input type="hidden" name="add_article" value="1">
                    <label>Titre<br>
                        <input type="text" name="title" required style="width:100%;padding:8px;margin-bottom:10px;">
                    </label><br>
                    <label>Contenu<br>
                        <textarea name="content" required style="width:100%;padding:8px;margin-bottom:10px;"></textarea>
                    </label><br>
                    <label>Photo<br>
                        <input type="file" name="photo" accept="image/*" style="margin-bottom:10px;">
                    </label><br>
                    <label>Page de destination<br>
                        <select name="page" required style="width:100%;padding:8px;margin-bottom:15px;">
                            <option value="">-- Choisir une page --</option>
                            <?php foreach ($pages as $key => $label): ?>
                                <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label><br>
                    <button type="submit" style="padding:8px 16px;">Ajouter l'article</button>
                </form>
            <?php endif; ?>
        </div>
        <div style="max-width:900px;margin:40px auto 0;">
            <h2>Visualiser, modifier ou supprimer les articles</h2>
            <form method="get" style="margin-bottom:20px;">
                <label>Choisir la page à afficher :
                    <select name="view_page" onchange="this.form.submit()" style="padding:6px;">
                        <?php foreach ($pages as $key => $label): ?>
                            <option value="<?php echo $key; ?>" <?php if ($selected_page === $key) echo 'selected'; ?>><?php echo htmlspecialchars($label); ?></option>
                        <?php endforeach; ?>
                        <option value="all" <?php if ($selected_page === 'all') echo 'selected'; ?>>Toutes les pages</option>
                    </select>
                </label>
            </form>
            <?php foreach ($view_pages as $pkey): ?>
                <?php
                $file = "articles_$pkey.txt";
                $articles = [];
                if (file_exists($file)) {
                    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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
                ?>
                <h3 style="margin-top:30px;"><?php echo htmlspecialchars($pages[$pkey]); ?></h3>
                <?php if (count($articles) === 0): ?>
                    <p style="color:#888;">Aucun article.</p>
                <?php else: ?>
                    <div style="display:block;">
                    <?php foreach ($articles as $i => $a): ?>
                        <article class="ajout-article">
                            <h4><?php echo htmlspecialchars($a['title']); ?></h4>
                            <?php if (!empty($a['image']) && file_exists($upload_dir . $a['image'])): ?>
                                <img src="<?php echo $upload_dir . htmlspecialchars($a['image']); ?>" alt="Photo" style="max-width:600px;max-height:400px;display:block;margin-bottom:8px;">
                            <?php endif; ?>
                            <p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p>
                            <?php if (!empty($a['date'])): ?>
                                <div style="color:#888;font-size:0.95em;margin-top:10px;">Publié le <?php echo date('d/m/Y H:i', strtotime($a['date'])); ?></div>
                            <?php endif; ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="delete_article" value="<?php echo $i; ?>">
                                <input type="hidden" name="del_page" value="<?php echo $pkey; ?>">
                                <button type="submit" onclick="return confirm('Supprimer cet article ?');" style="color:red;">Supprimer</button>
                            </form>
                            <a href="ajout.php?edit_page=<?php echo $pkey; ?>&edit_idx=<?php echo $i; ?>&view_page=<?php echo htmlspecialchars($selected_page); ?>" style="margin-left:10px;">Modifier</a>
                        </article>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </main>
</div>
</body>
</html>