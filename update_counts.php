<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $article_id = $data['articleId'] ?? '';
    $action = $data['action'] ?? '';

    if ($article_id && in_array($action, ['like', 'dislike'])) {
        $article_files = [
            'articles_index.txt',
            'articles_apres-match.txt',
            'articles_interview.txt',
            'articles_accreditation.txt',
            'articles_autre.txt'
        ];

        foreach ($article_files as $article_file) {
            if (file_exists($article_file)) {
                $lines = file($article_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $updated = false;

                foreach ($lines as &$line) {
                    $parts = explode('|', $line);
                    if (isset($parts[3]) && md5($parts[3]) === $article_id) {
                        // Update likes or dislikes
                        $parts[4] = isset($parts[4]) ? intval($parts[4]) : 0;
                        $parts[5] = isset($parts[5]) ? intval($parts[5]) : 0;

                        if ($action === 'like') {
                            $parts[4]++;
                        } elseif ($action === 'dislike') {
                            $parts[5]++;
                        }

                        $line = implode('|', $parts);
                        $updated = true;
                        break;
                    }
                }

                if ($updated) {
                    if (file_put_contents($article_file, implode(PHP_EOL, $lines) . PHP_EOL) === false) {
                        error_log("Failed to update file: $article_file");
                        echo json_encode(['success' => false, 'error' => 'Failed to update file']);
                        exit;
                    }
                    echo json_encode(['success' => true, 'likes' => $parts[4], 'dislikes' => $parts[5]]);
                    exit;
                }
            }
        }

        error_log("Article not found: $article_id");
        echo json_encode(['success' => false, 'error' => 'Article not found']);
        exit;
    }

    error_log("Invalid request: " . json_encode($data));
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
