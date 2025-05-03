<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $article_id = $data['articleId'] ?? '';
    $action = $data['action'] ?? '';

    if ($article_id && in_array($action, ['like', 'dislike'])) {
        $file = "counts/{$article_id}.json";
        $counts = file_exists($file) ? json_decode(file_get_contents($file), true) : ['likes' => 0, 'dislikes' => 0];

        if ($action === 'like') {
            $counts['likes']++;
        } elseif ($action === 'dislike') {
            $counts['dislikes']++;
        }

        // Ensure the counts directory exists
        if (!is_dir('counts')) {
            if (!mkdir('counts', 0777, true) && !is_dir('counts')) {
                echo json_encode(['success' => false, 'error' => 'Failed to create counts directory']);
                exit;
            }
        }

        // Save the updated counts
        if (file_put_contents($file, json_encode($counts)) === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to write to file']);
            exit;
        }

        echo json_encode(['success' => true, 'likes' => $counts['likes'], 'dislikes' => $counts['dislikes']]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
