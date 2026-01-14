-- Run in Adminer/DB to verify schema + values for gallery images
SHOW COLUMNS FROM `gallery`;

SELECT `id`, `title`, `image_url`, `image_path`, `is_public`, `sort_order`
FROM `gallery`
ORDER BY `id` DESC
LIMIT 20;

-- If your DB column is named path_url, check it too:
-- SELECT `id`, `title`, `path_url` FROM `gallery` ORDER BY `id` DESC LIMIT 20;

