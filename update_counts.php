<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $article_id = $data['articleId'] ?? '';
    $action = $data['action'] ?? '';
    $previous_action = $data['previousAction'] ?? '';

    if ($article_id && in_array($action, ['like', 'dislike'])) {
        $article_files = [
            'articles_apres-match.txt',
            'articles_interview.txt',
            'articles_accreditation.txt',
            'articles_autre.txt'
        ];

        foreach ($article_files as $article_file) {
            if (file_exists($article_file)) {
                $lines = file($article_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as &$line) {
                    $parts = explode('|', $line);
                    if (isset($parts[3]) && md5($parts[3]) === $article_id) {
                        $parts[4] = isset($parts[4]) ? (int)$parts[4] : 0; // Likes
                        $parts[5] = isset($parts[5]) ? (int)$parts[5] : 0; // Dislikes

                        // Adjust counters based on the current and previous actions
                        if ($previous_action === 'like') {
                            $parts[4]--; // Decrement likes
                        } elseif ($previous_action === 'dislike') {
                            $parts[5]--; // Decrement dislikes
                        }

                        if ($action === 'like') {
                            $parts[4]++; // Increment likes
                        } elseif ($action === 'dislike') {
                            $parts[5]++; // Increment dislikes
                        }

                        $line = implode('|', $parts);
                        file_put_contents($article_file, implode(PHP_EOL, $lines) . PHP_EOL);
                        echo json_encode(['success' => true, 'likes' => $parts[4], 'dislikes' => $parts[5]]);
                        exit;
                    }
                }
            }
        }

        echo json_encode(['success' => false, 'error' => 'Article not found']);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid request']);
?>
