<?php
// ===================== 数据读取 =====================
// 数据文件优先放在网站根目录(htdocs)外，避免被直接下载；不可写时降级到 htdocs 内
$candidates = array(dirname(__DIR__) . '/myspace_data.json', __DIR__ . '/myspace_data.json');
$dataFile = null;
$data = null;
foreach ($candidates as $c) {
    if (file_exists($c)) { $dataFile = $c; break; }
}
if ($dataFile && ($raw = @file_get_contents($dataFile))) {
    $tmp = json_decode($raw, true);
    if (is_array($tmp)) $data = $tmp;
}
// 默认数据（首次访问自动生成）
if (!$data) {
    $data = array(
        'meta' => array(
            'name' => '你的名字',
            'handle' => '@yourname',
            'desc' => '把我的作品、常用主页和联系方式，都整理在这一页。',
            'email' => 'hello@example.com'
        ),
        'links' => array(
            array('name' => '个人主页', 'sub' => '关于我与最近动态', 'cat' => '个人', 'url' => 'https://example.com/about', 'color' => 'linear-gradient(135deg,#8b5cf6,#6d5dfc)', 'icon' => 'home'),
            array('name' => '我的作品集', 'sub' => '设计、摄影与创作项目', 'cat' => '作品', 'url' => 'https://example.com/portfolio', 'color' => 'linear-gradient(135deg,#f59e0b,#f97316)', 'icon' => 'star'),
            array('name' => '个人博客', 'sub' => '记录想法与生活片段', 'cat' => '内容', 'url' => 'https://example.com/blog', 'color' => 'linear-gradient(135deg,#06b6d4,#0ea5e9)', 'icon' => 'doc'),
            array('name' => '小红书', 'sub' => '我的日常分享', 'cat' => '社交', 'url' => 'https://www.xiaohongshu.com', 'color' => 'linear-gradient(135deg,#ff2e4d,#ff5e62)', 'icon' => 'heart'),
            array('name' => '哔哩哔哩', 'sub' => '视频创作与日常 Vlog', 'cat' => '内容', 'url' => 'https://www.bilibili.com', 'color' => 'linear-gradient(135deg,#fb7299,#ff9a9e)', 'icon' => 'play'),
            array('name' => 'YouTube', 'sub' => '视频频道与长内容', 'cat' => '作品', 'url' => 'https://www.youtube.com', 'color' => 'linear-gradient(135deg,#ff4e45,#ff6a5b)', 'icon' => 'tube'),
            array('name' => 'X (Twitter)', 'sub' => '日常碎碎念', 'cat' => '社交', 'url' => 'https://x.com', 'color' => 'linear-gradient(135deg,#333333,#555555)', 'icon' => 'at'),
            array('name' => 'Instagram', 'sub' => '摄影与生活瞬间', 'cat' => '生活', 'url' => 'https://www.instagram.com', 'color' => 'linear-gradient(135deg,#f9ce34,#ee2a7b,#6228d7)', 'icon' => 'cam'),
            array('name' => '邮箱', 'sub' => '商务合作请联系', 'cat' => '生活', 'url' => 'mailto:hello@example.com', 'color' => 'linear-gradient(135deg,#10b981,#059669)', 'icon' => 'mail'),
            array('name' => 'GitHub', 'sub' => '代码与开源项目', 'cat' => '工具', 'url' => 'https://github.com', 'color' => 'linear-gradient(135deg,#4b5563,#1f2937)', 'icon' => 'git')
        )
    );
    // 尝试写入默认数据文件
    foreach ($candidates as $c) {
        if (@file_put_contents($c, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false) { $dataFile = $c; break; }
    }
}
$meta = isset($data['meta']) ? $data['meta'] : array();
$links = isset($data['links']) ? $data['links'] : array();
$navs = isset($data['navs']) && is_array($data['navs']) ? $data['navs'] : array();
$share = isset($data['share']) ? $data['share'] : array('title' => 'MY SPACE', 'text' => '');
if (!isset($meta['avatar'])) $meta['avatar'] = '';
if (empty($share['title'])) $share['title'] = 'MY SPACE';
if (empty($share['text'])) $share['text'] = '';
// 兼容旧数据：无 navs 时用默认
if (!$navs) {
    $navs = array(
        array('name' => '首页', 'icon' => 'home', 'action' => 'top', 'value' => ''),
        array('name' => '链接', 'icon' => 'link', 'action' => 'msg', 'value' => '所有链接已在首页展示，点击卡片即可访问'),
        array('name' => '上传', 'icon' => 'upload', 'action' => 'url', 'value' => 'admin.php'),
        array('name' => '电话', 'icon' => 'call', 'action' => 'tel', 'value' => '10086')
    );
}
$avatar = trim($meta['avatar']);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>MY SPACE · <?php echo htmlspecialchars(isset($meta['name']) ? $meta['name'] : ''); ?></title>
<style>
  :root {
    --bg: #f4f4f8;
    --card: #ffffff;
    --text: #23232b;
    --sub: #8a8a94;
    --line: rgba(139,92,246,.12);
    --grad1: #8b5cf6;
    --grad2: #ec4899;
  }
  * { margin: 0; padding: 0; box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
  body {
    font-family: -apple-system, BlinkMacSystemFont, "PingFang SC", "Microsoft YaHei", "Segoe UI", sans-serif;
    background: var(--bg); color: var(--text); min-height: 100vh;
  }
  .app { max-width: 430px; margin: 0 auto; padding: 14px 16px 100px; }
  .topbar { display: flex; align-items: center; justify-content: space-between; padding: 8px 4px 16px; }
  .brand { font-size: 20px; font-weight: 800; letter-spacing: .5px; }
  .brand em { font-style: normal; background: linear-gradient(135deg, var(--grad1), var(--grad2)); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
  .icon-btn {
    width: 36px; height: 36px; border-radius: 50%; background: var(--card); border: none;
    display: flex; align-items: center; justify-content: center; color: var(--text); cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
  }
  .icon-btn svg { width: 18px; height: 18px; }
  .profile-card {
    background: var(--card); border: 2px solid rgba(139,92,246,.15); border-radius: 20px;
    padding: 22px 18px 18px; box-shadow: 0 2px 12px rgba(0,0,0,.04);
  }
  .profile { display: flex; flex-direction: column; align-items: flex-start; text-align: left; }
  .profile-top { display: flex; align-items: center; gap: 14px; width: 100%; }
  .avatar {
    width: 70px; height: 70px; border-radius: 18px; flex: 0 0 auto;
    background: linear-gradient(135deg, var(--grad1), var(--grad2));
    display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 26px;
    box-shadow: 0 6px 16px rgba(139,92,246,.25);
  }
  img.avatar { object-fit: cover; }
  .profile-info { display: flex; flex-direction: column; align-items: flex-start; gap: 3px; }
  .name { font-size: 21px; font-weight: 800; }
  .badge {
    font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; color: var(--grad2);
    background: rgba(236,72,153,.1);
  }
  .intro { font-size: 13px; color: var(--sub); }
  .desc { margin-top: 10px; font-size: 13px; line-height: 1.6; color: var(--text); max-width: 320px; }
  .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 20px; width: 100%; }
  .btn {
    display: flex; align-items: center; justify-content: center; gap: 7px; padding: 13px 10px; border-radius: 999px;
    font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none;
    background: var(--card); color: var(--text);
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
  }
  .btn svg { width: 16px; height: 16px; flex: 0 0 auto; }
  .btn-grad {
    background: linear-gradient(135deg, var(--grad1), var(--grad2)); color: #fff;
    box-shadow: 0 6px 16px rgba(236,72,153,.22);
  }
  .section { margin-top: 20px; }
  .sec-head { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 12px; padding: 0 4px; }
  .sec-title { font-size: 17px; font-weight: 800; }
  .sec-count { font-size: 12px; color: var(--sub); }
  .search-wrap { position: relative; margin-bottom: 12px; }
  .search-wrap svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--sub); }
  .search {
    width: 100%; padding: 11px 14px 11px 40px; border: none; border-radius: 16px;
    background: var(--card); color: var(--text); outline: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.04);
  }
  .search::placeholder { color: #b9b9c2; }
  .tags { display: flex; gap: 8px; overflow-x: auto; padding: 2px 4px 10px; scrollbar-width: none; }
  .tags::-webkit-scrollbar { display: none; }
  .tag {
    flex: 0 0 auto; padding: 7px 16px; border-radius: 20px; background: var(--card); color: var(--sub);
    font-size: 13px; cursor: pointer; border: none; white-space: nowrap;
    box-shadow: 0 1px 6px rgba(0,0,0,.03);
  }
  .tag.active { background: linear-gradient(135deg, var(--grad1), var(--grad2)); color: #fff; font-weight: 600; box-shadow: 0 4px 12px rgba(139,92,246,.3); }
  .list { display: flex; flex-direction: column; gap: 10px; }
  .item {
    display: flex; align-items: center; gap: 13px; background: var(--card); border-radius: 22px;
    padding: 14px 16px; text-decoration: none; color: inherit;
    box-shadow: 0 4px 16px rgba(139,92,246,.08);
    transition: transform .12s ease, box-shadow .12s ease;
  }
  .item:active { transform: scale(.985); box-shadow: 0 1px 4px rgba(0,0,0,.06); }
  .item-icon {
    width: 44px; height: 44px; border-radius: 13px; flex: 0 0 auto;
    display: flex; align-items: center; justify-content: center; color: #fff;
  }
  .item-icon svg { width: 21px; height: 21px; }
  .item-txt { flex: 1; min-width: 0; }
  .item-name { font-size: 14px; font-weight: 700; }
  .item-sub { font-size: 12px; color: var(--sub); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .item-arrow { color: #c9c9d1; flex: 0 0 auto; }
  .item-arrow svg { width: 15px; height: 15px; }
  .empty { text-align: center; color: var(--sub); font-size: 13px; padding: 30px 0; display: none; }
  .tabbar {
    position: fixed; bottom: 20px; left: 50%; transform: translateX(-50%);
    background: var(--card); border-radius: 28px;
    display: flex; justify-content: space-around; align-items: center;
    box-shadow: 0 8px 24px rgba(139,92,246,.15);
    padding: 0 10px; height: 56px;
  }
  .tab { display: flex; flex-direction: column; align-items: center; gap: 3px; font-size: 10px; color: var(--sub); background: none; border: none; cursor: pointer; padding: 4px 18px; border-radius: 18px; transition: background .15s ease; }
  .tab svg { width: 20px; height: 20px; }
  .tab.active { color: var(--grad1); font-weight: 600; }
  .tab.active { background: rgba(139,92,246,.08); }
  .admin-link { text-align:center; margin-top: 26px; font-size: 11px; color:#c3c3cd; text-decoration:none; display:block; }
</style>
</head>
<body>
<div class="app">
  <div class="topbar">
    <div class="brand">MY <em>SPACE</em></div>
    <button class="icon-btn" id="shareBtn" title="分享" type="button">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.6" y1="13.5" x2="15.4" y2="17.5"/><line x1="15.4" y1="6.5" x2="8.6" y2="10.5"/></svg>
    </button>
  </div>

  <div class="profile-card">
    <div class="profile">
      <div class="profile-top">
        <?php if ($avatar): ?>
          <img class="avatar" src="<?php echo htmlspecialchars($avatar); ?>" alt="头像" referrerpolicy="no-referrer">
        <?php else: ?>
          <div class="avatar"><?php echo htmlspecialchars(mb_substr(isset($meta['name']) ? $meta['name'] : '你', 0, 1)); ?></div>
        <?php endif; ?>
        <div class="profile-info">
          <div class="name"><?php echo htmlspecialchars(isset($meta['name']) ? $meta['name'] : ''); ?></div>
          <span class="badge">独立创作者 · 中国</span>
        </div>
      </div>
      <div class="desc"><?php echo htmlspecialchars(isset($meta['desc']) ? $meta['desc'] : ''); ?></div>
      <div class="actions">
        <button class="btn btn-ghost" id="copyEmail" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
          复制邮箱
        </button>
        <a class="btn btn-grad" id="contactBtn" href="mailto:<?php echo htmlspecialchars(isset($meta['email']) ? $meta['email'] : ''); ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
          联系我
        </a>
      </div>
    </div>
  </div>

  <div class="section">
    <div class="sec-head">
      <span class="sec-title">网址导航</span>
      <span class="sec-count" id="countText"></span>
    </div>
    <div class="search-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input class="search" id="searchInput" type="text" placeholder="搜索网址或关键词">
    </div>
    <div class="tags" id="tags"></div>
    <div class="list" id="list"></div>
    <div class="empty" id="empty">没有找到相关入口</div>
  </div>

  <a class="admin-link" href="admin.php">管 理</a>
</div>

<div class="tabbar" id="tabbar">
  <?php
  $NAV_ICON_PATH = array(
    'home' => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    'link' => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
    'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>',
    'call' => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'mail' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
    'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'star' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"/>',
    'heart' => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
    'doc' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
    'cam' => '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>'
  );
  $first = true;
  foreach ($navs as $nv) {
      $nicon = isset($nv['icon']) && isset($NAV_ICON_PATH[$nv['icon']]) ? $nv['icon'] : 'home';
      $nact = isset($nv['action']) ? $nv['action'] : 'msg';
      $nval = isset($nv['value']) ? $nv['value'] : '';
      echo '<button class="tab' . ($first ? ' active' : '') . '" type="button" data-act="' . htmlspecialchars($nact, ENT_QUOTES) . '" data-val="' . htmlspecialchars($nval, ENT_QUOTES) . '">';
      echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $NAV_ICON_PATH[$nicon] . '</svg>';
      echo htmlspecialchars($nv['name']);
      echo '</button>';
      $first = false;
  }
  ?>
</div>

<script>
// ===== 服务端注入数据 =====
<?php
$JSON_HEX = JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
?>
var DATA = <?php echo json_encode(array('meta' => $meta, 'links' => $links), $JSON_HEX); ?>;
var SHARE = <?php echo json_encode($share, $JSON_HEX); ?>;
var MY = DATA.meta || {};
var LINKS = DATA.links || [];

var esc = function(s) {
  return String(s == null ? '' : s)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
};
// 只允许 http/https/mailto/tel，其余一律置为 #，避免 javascript: 等伪协议注入
var safeUrl = function(u) {
  u = String(u || '').trim();
  if (/^(https?:\/\/|mailto:|tel:)/i.test(u)) return u;
  return '#';
};

var ICONS = {
  home: '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
  star: '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26"/>',
  doc: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
  heart: '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
  play: '<polygon points="5 3 19 12 5 21 5 3"/>',
  tube: '<path d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33A2.78 2.78 0 0 0 3.4 19c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.25 29 29 0 0 0-.46-5.33z"/><polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02"/>',
  at: '<circle cx="12" cy="12" r="4"/><path d="M16 8v5a3 3 0 0 0 6 0v-1a10 10 0 1 0-3.92 7.94"/>',
  cam: '<path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/>',
  mail: '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
  git: '<path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"/>'
};

var activeTag = '全部';
var CATS = ['全部','个人','作品','内容','社交','生活','工具'];

function buildTags() {
  document.getElementById('tags').innerHTML = CATS.map(function(c){
    return '<button class="tag' + (c === activeTag ? ' active' : '') + '" data-tag="' + c + '" type="button">' + c + '</button>';
  }).join('');
  document.querySelectorAll('#tags .tag').forEach(function(t){
    t.addEventListener('click', function(){
      document.querySelectorAll('#tags .tag').forEach(function(x){ x.classList.remove('active'); });
      t.classList.add('active');
      activeTag = t.dataset.tag;
      render();
    });
  });
}

function render() {
  var kw = (document.getElementById('searchInput').value || '').trim().toLowerCase();
  var filtered = LINKS.filter(function(l){
    return (activeTag === '全部' || l.cat === activeTag) &&
      (!kw || (l.name||'').toLowerCase().indexOf(kw) >= 0 || (l.sub||'').toLowerCase().indexOf(kw) >= 0);
  });
  document.getElementById('list').innerHTML = filtered.map(function(l){
    var ic = ICONS[l.icon] || ICONS.home;
    return '<a class="item" href="' + safeUrl(l.url) + '" target="_blank" rel="noopener">' +
      '<div class="item-icon" style="background:' + esc(l.color) + '">' +
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + ic + '</svg></div>' +
      '<div class="item-txt"><div class="item-name">' + esc(l.name) + '</div><div class="item-sub">' + esc(l.sub) + '</div></div>' +
      '<div class="item-arrow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></div></a>';
  }).join('');
  document.getElementById('empty').style.display = filtered.length ? 'none' : 'block';
  document.getElementById('countText').textContent = filtered.length + ' 个入口';
}

document.getElementById('searchInput').addEventListener('input', render);

document.getElementById('copyEmail').addEventListener('click', function(){
  var email = MY.email || '';
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(email).then(function(){ alert('邮箱已复制：' + email); });
  } else {
    prompt('请手动复制邮箱：', email);
  }
});

document.getElementById('shareBtn').addEventListener('click', function(){
  var url = location.href;
  var st = SHARE.title || 'MY SPACE';
  var tx = SHARE.text || '';
  tx = tx.replace(/\{name\}/g, MY.name || '');
  if (!st) st = 'MY SPACE';
  if (navigator.share) {
    navigator.share({ title: st, text: tx || (MY.name + ' 的个人主页'), url: url });
  } else if (navigator.clipboard) {
    navigator.clipboard.writeText(url);
    alert('链接已复制：' + url);
  }
});

function navAction(btn, act, val) {
  document.querySelectorAll('.tab').forEach(function(x){ x.classList.remove('active'); });
  btn.classList.add('active');
  if (act === 'top') {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } else if (act === 'msg' && val) {
    alert(val);
  } else if (act === 'url' && val) {
    var target = String(val).trim();
    if (/^(https?:|mailto:|tel:)/i.test(target)) {
      window.open(target, target.indexOf('http') === 0 ? '_blank' : '_self');
    } else if (!/^(javascript|data|vbscript):/i.test(target)) {
      window.open(target, '_self');
    }
  } else if (act === 'tel' && val) {
    location.href = 'tel:' + encodeURIComponent(val);
  } else if (act === 'mailto' && val) {
    location.href = 'mailto:' + encodeURIComponent(val);
  }
}
document.querySelectorAll('#tabbar .tab').forEach(function(btn){
  btn.addEventListener('click', function(){
    navAction(btn, btn.getAttribute('data-act') || 'msg', btn.getAttribute('data-val') || '');
  });
});

buildTags();
render();
</script>
</body>
</html>
