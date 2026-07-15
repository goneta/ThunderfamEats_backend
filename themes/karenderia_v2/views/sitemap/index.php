<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <?php foreach ($urls as $url): ?>
        <url>
            <loc><?php echo CHtml::encode($url['loc']); ?></loc>
            <lastmod><?php echo CHtml::encode($url['lastmod']); ?></lastmod>
            <changefreq><?php echo CHtml::encode($url['changefreq']); ?></changefreq>
            <priority><?php echo CHtml::encode($url['priority']); ?></priority>
        </url>
    <?php endforeach; ?>
</urlset>