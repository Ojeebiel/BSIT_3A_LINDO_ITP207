<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vox Latest Articles</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 2rem;
        }
        .container {
            max-width: 1000px;
            width: 100%;
            margin: 0 auto; 
        }
        h1 {
            text-align: center;
            color: #1a1a1a;
            margin-bottom: 2rem;
        }
        ul {
            list-style: none;
            padding: 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            justify-content: center;
        }
        li {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            padding: 1rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        li:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        a.title {
            text-decoration: none;
            color: #1a73e8;
            font-weight: bold;
            font-size: 1.1rem;
        }
        a.title:hover {
            text-decoration: underline;
        }
        small.date {
            color: #888;
            font-size: 0.85rem;
        }
        p.summary {
            margin-top: 0.5rem;
            color: #555;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <?php

        // PART 1: XML PARSING / CONVERSION
        $rss_url = "https://www.vox.com/rss/index.xml";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $rss_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64)");
        $data = curl_exec($ch);
        curl_close($ch);

        if ($data === false) {
            echo "<p>Failed to fetch feed.</p>";
            exit;
        }
        $rss = simplexml_load_string($data);
        if ($rss === false) {
            echo "<p>Could not parse feed as XML.</p>";
            exit;
        }
        $ns = $rss->getNamespaces(true);
        $rss->registerXPathNamespace('atom', $ns[''] ?? 'http://www.w3.org/2005/Atom');
        $items = $rss->xpath('//atom:entry');
        if (!$items) {
            echo "<p>No entries found in feed.</p>";
            exit;
        }

        $articles = [];
        $count = 0;
        foreach ($items as $entry) {
            $title = (string)$entry->title;
            $link  = (string)$entry->link['href'];
            $date  = isset($entry->updated) ? (string)$entry->updated : "";
            $desc  = isset($entry->summary) ? (string)$entry->summary : "";

            $articles[] = [
                'title' => $title,
                'link'  => $link,
                'date'  => $date,
                'desc'  => $desc
            ];

            $count++;
            if ($count >= 10) break;
        }

        // PART 2: HTML RENDERING
        echo "<h1>Vox — Latest Articles</h1>";
        echo "<ul>";

        foreach ($articles as $article) {
            echo "<li>";
            echo "<a href='" . htmlspecialchars($article['link']) . "' class='title' target='_blank'>" . htmlspecialchars($article['title']) . "</a><br>";
            if ($article['date']) {
                echo "<small class='date'>" . date('Y-m-d H:i:s', strtotime($article['date'])) . "</small>";
            }
            if ($article['desc']) {
                echo "<p class='summary'>" . substr(strip_tags($article['desc']), 0, 200) . "...</p>";
            }
            echo "</li>";
        }

        echo "</ul>";
        ?>
    </div>
</body>
</html>
