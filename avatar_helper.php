<?php
// Renders a user's avatar image, or an initials circle if none is set.
// Usage: render_avatar($username, $avatarPath, $sizePx = 40)
function render_avatar($username, $avatarPath, $size = 40) {
    $style = "width:{$size}px;height:{$size}px;";
    if ($avatarPath && file_exists(__DIR__ . "/" . $avatarPath)) {
        echo '<img class="avatar-img" style="' . $style . '" src="' . htmlspecialchars($avatarPath) . '" alt="">';
    } else {
        $initial = strtoupper(substr($username, 0, 1));
        $fontSize = round($size * 0.42);
        echo '<div class="avatar-fallback" style="' . $style . 'font-size:' . $fontSize . 'px;">' . htmlspecialchars($initial) . '</div>';
    }
}
