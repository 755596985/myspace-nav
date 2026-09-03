<?php
session_start();
// CSRF 令牌：全站表单统一校验，防止跨站请求伪造
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];

// ===================== 数据读写 =====================
$candidates = array(dirname(__DIR__) . '/myspace_data.json', __DIR__ . '/myspace_data.json');
function default_data() {
    return array(
        'admin_password' => 'admin888',
        'meta' => array(
            'name' => '你的名字',
            'handle' => '@yourname',
            'desc' => '把我的作品、常用主页和联系方式，都整理在这一页。',
            'email' => 'hello@example.com',
            'avatar' => ''
        ),
        'navs' => array(
            array('name' => '首页', 'icon' => 'home', 'action' => 'top', 'value' => ''),
            array('name' => '链接', 'icon' => 'link', 'action' => 'msg', 'value' => '所有链接已在首页展示，点击卡片即可访问'),
            array('name' => '上传', 'icon' => 'upload', 'action' => 'url', 'value' => 'admin.php'),
            array('name' => '电话', 'icon' => 'call', 'action' => 'tel', 'value' => '10086')
        ),
        'share' => array(
            'title' => 'MY SPACE',
            'text' => '{name} 的个人主页，欢迎来看看'
        ),
        'links' => array(
            array('name' => '个人主页', 'sub' => '关于我与最近动态', 'cat' => '个人', 'url' => 'https://example.com/about', 'color' => 'violet', 'icon' => 'home'),
            array('name' => '我的作品集', 'sub' => '设计、摄影与创作项目', 'cat' => '作品', 'url' => 'https://example.com/portfolio', 'color' => 'orange', 'icon' => 'star'),
            array('name' => '个人博客', 'sub' => '记录想法与生活片段', 'cat' => '内容', 'url' => 'https://example.com/blog', 'color' => 'sky', 'icon' => 'doc'),
            array('name' => '小红书', 'sub' => '我的日常分享', 'cat' => '社交', 'url' => 'https://www.xiaohongshu.com', 'color' => 'red', 'icon' => 'heart'),
            array('name' => '哔哩哔哩', 'sub' => '视频创作与日常 Vlog', 'cat' => '内容', 'url' => 'https://www.bilibili.com', 'color' => 'pink', 'icon' => 'play'),
            array('name' => 'YouTube', 'sub' => '视频频道与长内容', 'cat' => '作品', 'url' => 'https://www.youtube.com', 'color' => 'yt', 'icon' => 'tube'),
            array('name' => 'X (Twitter)', 'sub' => '日常碎碎念', 'cat' => '社交', 'url' => 'https://x.com', 'color' => 'dark', 'icon' => 'at'),
            array('name' => 'Instagram', 'sub' => '摄影与生活瞬间', 'cat' => '生活', 'url' => 'https://www.instagram.com', 'color' => 'insta', 'icon' => 'cam'),
            array('name' => '邮箱', 'sub' => '商务合作请联系', 'cat' => '生活', 'url' => 'mailto:hello@example.com', 'color' => 'green', 'icon' => 'mail'),
            array('name' => 'GitHub', 'sub' => '代码与开源项目', 'cat' => '工具', 'url' => 'https://github.com', 'color' => 'gh', 'icon' => 'git')
        )
    );
}
function load_data() {
    global $candidates;
    foreach ($candidates as $c) {
        if (file_exists($c)) {
            $raw = @file_get_contents($c);
            $d = json_decode($raw, true);
            if (is_array($d)) {
                if (!isset($d['admin_password'])) $d['admin_password'] = 'admin888';
                if (!isset($d['meta'])) $d['meta'] = array('name'=>'你的名字','handle'=>'@yourname','desc'=>'','email'=>'');
                if (!isset($d['meta']['avatar'])) $d['meta']['avatar'] = '';
                if (!isset($d['navs'])) $d['navs'] = array();
                if (!isset($d['share'])) $d['share'] = array('title'=>'MY SPACE','text'=>'');
                if (!isset($d['links'])) $d['links'] = array();
                return $d;
            }
        }
    }
    return default_data();
}
function save_data($data) {
    global $candidates;
    // 密码安全：明文密码统一升级为 password_hash，禁止明文落盘
    if (isset($data['admin_password']) && $data['admin_password'] !== '') {
        $pinfo = password_get_info($data['admin_password']);
        if ($pinfo['algo'] === 0) {
            $data['admin_password'] = password_hash($data['admin_password'], PASSWORD_DEFAULT);
        }
    }
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    foreach ($candidates as $c) {
        if (@file_put_contents($c, $json) !== false) return true;
    }
    return false;
}

// 颜色 / 图标 / 分类 选项
$COLORS = array(
    'violet' => '紫', 'orange' => '橙', 'sky' => '蓝', 'red' => '红',
    'pink' => '粉', 'yt' => '红2', 'dark' => '黑', 'green' => '绿',
    'insta' => '彩', 'gh' => '灰'
);
$COLOR_CSS = array(
    'violet' => 'linear-gradient(135deg,#8b5cf6,#6d5dfc)',
    'orange' => 'linear-gradient(135deg,#f59e0b,#f97316)',
    'sky'    => 'linear-gradient(135deg,#06b6d4,#0ea5e9)',
    'red'    => 'linear-gradient(135deg,#ff2e4d,#ff5e62)',
    'pink'   => 'linear-gradient(135deg,#fb7299,#ff9a9e)',
    'yt'     => 'linear-gradient(135deg,#ff4e45,#ff6a5b)',
    'dark'   => 'linear-gradient(135deg,#333333,#555555)',
    'green'  => 'linear-gradient(135deg,#10b981,#059669)',
    'insta'  => 'linear-gradient(135deg,#f9ce34,#ee2a7b,#6228d7)',
    'gh'     => 'linear-gradient(135deg,#4b5563,#1f2937)'
);
$ICONS = array(
    'home' => '首页', 'star' => '星形', 'doc' => '文档', 'heart' => '爱心',
    'play' => '播放', 'tube' => '视频', 'at' => '@', 'cam' => '相机',
    'mail' => '邮箱', 'git' => '代码'
);
$CATS = array('个人','作品','内容','社交','生活','工具');
// 兼容旧颜色字符串
function color_key($c) {
    global $COLOR_CSS;
    foreach ($COLOR_CSS as $k => $v) { if ($c === $k || $c === $v) return $k; }
    return 'violet';
}

$data = load_data();
$msg = '';
$error = '';

// ===================== 操作处理 =====================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 统一 CSRF 校验
    $csrf_ok = isset($_POST['csrf']) && hash_equals($csrf, $_POST['csrf']);
    if (!$csrf_ok) {
        $error = '会话校验失败，请刷新页面后重试';
    }
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($csrf_ok && $action === 'login') {
        $pw = isset($_POST['password']) ? $_POST['password'] : '';
        $stored = isset($data['admin_password']) ? $data['admin_password'] : 'admin888';
        // 兼容旧版明文存储：新数据存 password_hash；旧明文登录成功后自动升级
        $info = password_get_info($stored);
        $ok = ($info['algo'] !== 0) ? password_verify($pw, $stored) : hash_equals($stored, $pw);
        if ($ok) {
            $_SESSION['myspace_admin'] = true;
            if ($info['algo'] === 0 && $stored !== '') {
                $data['admin_password'] = password_hash($pw, PASSWORD_DEFAULT);
                save_data($data);
            }
        } else {
            $error = '密码错误';
        }
    }

    if ($csrf_ok && $action === 'logout') {
        unset($_SESSION['myspace_admin']);
    }

    if ($csrf_ok && $action === 'save' && !empty($_SESSION['myspace_admin'])) {
        $meta = array(
            'name'  => trim(isset($_POST['name']) ? $_POST['name'] : ''),
            'handle'=> trim(isset($_POST['handle']) ? $_POST['handle'] : ''),
            'desc'  => trim(isset($_POST['desc']) ? $_POST['desc'] : ''),
            'email' => trim(isset($_POST['email']) ? $_POST['email'] : ''),
            'avatar'=> trim(isset($_POST['avatar']) ? $_POST['avatar'] : '')
        );
        if ($meta['name'] === '') $meta['name'] = '你的名字';
        $data['meta'] = $meta;

        // 头像文件上传（可选）
        if (isset($_FILES['avatar_file']) && is_uploaded_file($_FILES['avatar_file']['tmp_name']) && $_FILES['avatar_file']['error'] === UPLOAD_ERR_OK) {
            $updir = __DIR__ . '/uploads';
            if (!is_dir($updir)) {
                @mkdir($updir, 0755, true);
                // uploads 仅用于存放图片，禁止执行任何服务端脚本（纵深防御）
                @file_put_contents($updir . '/.htaccess', "<FilesMatch \"\\.(php|phtml|php5|php7|cgi|pl|py)$\">\n  Require all denied\n</FilesMatch>\n");
            }
            $ext = strtolower(pathinfo($_FILES['avatar_file']['name'], PATHINFO_EXTENSION));
            $allow = array('jpg','jpeg','png','gif','webp');
            // 校验文件内容为真实图片（防止伪装图片的木马文件）
            $imgInfo = @getimagesize($_FILES['avatar_file']['tmp_name']);
            $mimeOk = $imgInfo && in_array(strtolower($imgInfo['mime']), array('image/jpeg','image/png','image/gif','image/webp'));
            if (in_array($ext, $allow) && $mimeOk && is_dir($updir)) {
                $fn = 'avatar_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                if (@move_uploaded_file($_FILES['avatar_file']['tmp_name'], $updir . '/' . $fn)) {
                    $data['meta']['avatar'] = 'uploads/' . $fn;
                }
            } else {
                $error = '头像上传失败：仅支持 jpg/png/gif/webp 真实图片';
            }
        }

        // 底部导航栏
        $navNames  = isset($_POST['nn']) ? $_POST['nn'] : array();
        $navIcons  = isset($_POST['ni']) ? $_POST['ni'] : array();
        $navActs   = isset($_POST['na']) ? $_POST['na'] : array();
        $navVals   = isset($_POST['nv']) ? $_POST['nv'] : array();
        $navDels   = isset($_POST['ndel']) ? $_POST['ndel'] : array();
        $navs = array();
        $ncount = count($navNames);
        for ($k = 0; $k < $ncount; $k++) {
            if (!empty($navDels[$k])) continue;
            $nm = trim($navNames[$k]);
            if ($nm === '') continue;
            $navs[] = array(
                'name' => $nm,
                'icon' => isset($navIcons[$k]) && in_array($navIcons[$k], array('home','link','upload','call','mail','user','star','heart','doc','cam')) ? $navIcons[$k] : 'home',
                'action' => isset($navActs[$k]) && in_array($navActs[$k], array('top','msg','url','tel','mailto')) ? $navActs[$k] : 'msg',
                'value' => trim(isset($navVals[$k]) ? $navVals[$k] : '')
            );
        }
        $data['navs'] = $navs;

        // 分享设置
        $shareTitle = trim(isset($_POST['share_title']) ? $_POST['share_title'] : '');
        $shareText  = trim(isset($_POST['share_text']) ? $_POST['share_text'] : '');
        if ($shareTitle === '') $shareTitle = 'MY SPACE';
        $data['share'] = array('title' => $shareTitle, 'text' => $shareText);

        // 可选修改后台密码
        $np = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        if ($np !== '' && strlen($np) >= 4) {
            $data['admin_password'] = $np;
        }

        // 链接列表
        $names  = isset($_POST['n']) ? $_POST['n'] : array();
        $subs   = isset($_POST['s']) ? $_POST['s'] : array();
        $cats   = isset($_POST['c']) ? $_POST['c'] : array();
        $urls   = isset($_POST['u']) ? $_POST['u'] : array();
        $colors = isset($_POST['co']) ? $_POST['co'] : array();
        $icons  = isset($_POST['i']) ? $_POST['i'] : array();
        $dels   = isset($_POST['del']) ? $_POST['del'] : array();

        $links = array();
        $count = count($names);
        for ($k = 0; $k < $count; $k++) {
            if (!empty($dels[$k])) continue;
            $name = trim($names[$k]);
            $url  = trim($urls[$k]);
            if ($name === '' && $url === '') continue;
            if ($name === '') $name = '未命名';
            // URL 协议白名单：禁止 javascript:/data:/vbscript: 等危险伪协议
            if ($url !== '' && preg_match('/^(javascript|data|vbscript)\s*:/i', $url)) {
                $url = '';
            }
            $ck = color_key(isset($colors[$k]) ? $colors[$k] : 'violet');
            $links[] = array(
                'name' => $name,
                'sub'  => trim(isset($subs[$k]) ? $subs[$k] : ''),
                'cat'  => in_array(isset($cats[$k]) ? $cats[$k] : '', $CATS) ? $cats[$k] : '个人',
                'url'  => $url,
                'color'=> $COLOR_CSS[$ck],
                'icon' => isset($icons[$k]) && isset($ICONS[$icons[$k]]) ? $icons[$k] : 'home'
            );
        }
        $data['links'] = $links;

        if (save_data($data)) {
            $msg = '保存成功，首页已更新';
        } else {
            $error = '保存失败：数据目录不可写，请检查文件权限';
        }
    }
}

$logged = !empty($_SESSION['myspace_admin']);
if (!$logged) {
    // ============ 登录页 ============
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MY SPACE · 管理登录</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Microsoft YaHei",sans-serif; background:#f4f4f8; min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .box { width:min(92vw,360px); background:#fff; border-radius:20px; padding:30px 24px; box-shadow:0 8px 30px rgba(0,0,0,.06); }
  h1 { font-size:20px; text-align:center; margin-bottom:6px; }
  h1 em { font-style:normal; background:linear-gradient(135deg,#8b5cf6,#ec4899); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
  .tip { text-align:center; font-size:12px; color:#8a8a94; margin-bottom:20px; }
  input { width:100%; padding:12px 14px; border:1px solid #ececf1; border-radius:12px; font-size:15px; outline:none; margin-bottom:14px; }
  input:focus { border-color:#8b5cf6; }
  button { width:100%; padding:12px; border:none; border-radius:12px; font-size:15px; font-weight:600; color:#fff; background:linear-gradient(135deg,#8b5cf6,#ec4899); cursor:pointer; }
  .err { color:#e11d48; font-size:13px; text-align:center; margin-bottom:10px; }
  .back { display:block; text-align:center; font-size:12px; color:#b9b9c2; margin-top:14px; text-decoration:none; }
</style>
</head>
<body>
<div class="box">
  <h1>MY <em>SPACE</em> 管理</h1>
  <div class="tip">默认密码：admin888，登录后请尽快修改</div>
  <?php if ($error) echo '<div class="err">'.$error.'</div>'; ?>
  <form method="post">
    <input type="hidden" name="action" value="login">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">
    <input type="password" name="password" placeholder="输入管理密码" autocomplete="current-password" required>
    <button type="submit">登 录</button>
  </form>
  <a class="back" href="index.php">返回首页</a>
</div>
</body>
</html>
<?php exit; }

// ============ 管理界面 ============
?><!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MY SPACE · 后台管理</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:-apple-system,BlinkMacSystemFont,"PingFang SC","Microsoft YaHei",sans-serif; background:#f4f4f8; color:#23232b; padding:16px 12px 60px; }
  .wrap { max-width:620px; margin:0 auto; }
  .head { display:flex; align-items:center; justify-content:space-between; padding:6px 4px 14px; }
  .head h1 { font-size:20px; }
  .head h1 em { font-style:normal; background:linear-gradient(135deg,#8b5cf6,#ec4899); -webkit-background-clip:text; background-clip:text; -webkit-text-fill-color:transparent; }
  .head a, .head button { font-size:13px; color:#8b5cf6; text-decoration:none; background:none; border:none; cursor:pointer; }
  .msg { background:#ecfdf5; color:#047857; font-size:13px; padding:10px 14px; border-radius:10px; margin-bottom:12px; }
  .err { background:#fff1f2; color:#e11d48; font-size:13px; padding:10px 14px; border-radius:10px; margin-bottom:12px; }
  .card { background:#fff; border-radius:16px; padding:16px; margin-bottom:14px; box-shadow:0 2px 10px rgba(0,0,0,.04); }
  .card h2 { font-size:15px; margin-bottom:12px; }
  .field { margin-bottom:12px; }
  .field label { display:block; font-size:12px; color:#8a8a94; margin-bottom:6px; }
  .field input, .field select, .field textarea {
    width:100%; padding:10px 12px; border:1px solid #ececf1; border-radius:10px; font-size:14px; outline:none; background:#fff; font-family:inherit;
  }
  .field input:focus, .field textarea:focus { border-color:#8b5cf6; }
  .grid2 { display:grid; grid-template-columns:1fr 1fr; gap:0 12px; }
  .link-row { border:1px solid #ececf1; border-radius:12px; padding:12px; margin-bottom:10px; background:#fafafe; }
  .link-row .top { display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; }
  .link-row .top b { font-size:13px; }
  .del-label { font-size:13px; color:#e11d48; display:flex; align-items:center; gap:4px; cursor:pointer; }
  .link-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
  .save-btn { width:100%; padding:13px; border:none; border-radius:12px; font-size:15px; font-weight:700; color:#fff; background:linear-gradient(135deg,#8b5cf6,#ec4899); cursor:pointer; }
  .add-btn { width:100%; padding:11px; border:1px dashed #c4b5fd; border-radius:12px; font-size:14px; color:#8b5cf6; background:#fff; cursor:pointer; margin-bottom:8px; }
  .preview { display:block; text-align:center; font-size:13px; color:#8b5cf6; text-decoration:none; margin:12px 0; }
  .link-row.del { opacity:.5; }
  .hint { font-size:12px; color:#b9b9c2; margin-top:8px; line-height:1.6; }
</style>
</head>
<body>
<div class="wrap">
  <div class="head">
    <h1>MY <em>SPACE</em> 后台</h1>
    <form method="post" style="display:inline"><input type="hidden" name="action" value="logout"><input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>"><button type="submit">退出登录</button></form>
  </div>

  <?php if ($msg) echo '<div class="msg">'.$msg.'</div>'; ?>
  <?php if ($error) echo '<div class="err">'.$error.'</div>'; ?>

  <a class="preview" href="index.php" target="_blank">← 查看网站首页效果</a>

  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES); ?>">

    <div class="card">
      <h2>基本资料</h2>
      <div class="field"><label>头像（可填图片网址，或下方直接上传图片文件）</label><input name="avatar" value="<?php echo htmlspecialchars(isset($data['meta']['avatar']) ? $data['meta']['avatar'] : ''); ?>" placeholder="https://... 或留空用上传"></div>
      <div class="field">
        <label>上传头像文件（jpg/png/gif/webp，上传后自动生效）</label>
        <input type="file" name="avatar_file" accept="image/*">
        <?php if (!empty($data['meta']['avatar'])): ?><div class="hint">当前头像：<?php echo htmlspecialchars($data['meta']['avatar']); ?>；若想恢复为"首字母头像"，请清空上方网址框并上传留空后保存。</div><?php endif; ?>
      </div>
      <div class="grid2">
        <div class="field"><label>名字</label><input name="name" value="<?php echo htmlspecialchars($data['meta']['name']); ?>"></div>
        <div class="field"><label>账号（@xxx）</label><input name="handle" value="<?php echo htmlspecialchars($data['meta']['handle']); ?>"></div>
      </div>
      <div class="field"><label>一句话简介</label><textarea name="desc" rows="2"><?php echo htmlspecialchars($data['meta']['desc']); ?></textarea></div>
      <div class="field"><label>邮箱（复制邮箱 / 联系我按钮使用）</label><input name="email" value="<?php echo htmlspecialchars($data['meta']['email']); ?>"></div>
    </div>

    <div class="card">
      <h2>底部导航栏（悬浮窗按钮，可增删改）</h2>
      <div class="hint">动作说明：顶部=回到页面顶部；提示=点击弹出一句话（在下方"内容"填写）；网址=跳转到链接（填完整URL）；拨号=拨打电话（填号码）；邮件=发邮件（填邮箱）。留空内容则点击仅切换高亮。</div>
      <div id="navRows">
        <?php
        $nidx = 0;
        $NAV_ACTIONS = array('top' => '回顶部', 'msg' => '弹提示', 'url' => '跳转网址', 'tel' => '拨打电话', 'mailto' => '发送邮件');
        $NAV_ICONS = array('home' => '房子', 'link' => '链接', 'upload' => '上传', 'call' => '电话', 'mail' => '邮箱', 'user' => '用户', 'star' => '星形', 'heart' => '爱心', 'doc' => '文档', 'cam' => '相机');
        foreach ((isset($data['navs']) && is_array($data['navs'])) ? $data['navs'] : array() as $nav) {
            $navIcon = isset($nav['icon']) && isset($NAV_ICONS[$nav['icon']]) ? $nav['icon'] : 'home';
            $navAct  = isset($nav['action']) && isset($NAV_ACTIONS[$nav['action']]) ? $nav['action'] : 'msg';
            echo '<div class="link-row" data-idx="'.$nidx.'">';
            echo '<div class="top"><b>导航 ' . ($nidx + 1) . '</b><label class="del-label"><input type="checkbox" name="ndel[]" value="1" onchange="this.closest(\'.link-row\').classList.toggle(\'del\',this.checked)"> 删除此项</label></div>';
            echo '<div class="link-grid">';
            echo '<div class="field"><label>文字</label><input name="nn[]" value="'.htmlspecialchars($nav['name']).'"></div>';
            echo '<div class="field"><label>图标</label><select name="ni[]">';
            foreach ($NAV_ICONS as $k => $cn) echo '<option value="'.$k.'"'.($navIcon===$k?' selected':'').'>'.$cn.'</option>';
            echo '</select></div>';
            echo '<div class="field"><label>点击动作</label><select name="na[]">';
            foreach ($NAV_ACTIONS as $k => $cn) echo '<option value="'.$k.'"'.($navAct===$k?' selected':'').'>'.$cn.'</option>';
            echo '</select></div>';
            echo '<div class="field"><label>内容（提示语/网址/号码）</label><input name="nv[]" value="'.htmlspecialchars(isset($nav['value']) ? $nav['value'] : '').'"></div>';
            echo '</div></div>';
            $nidx++;
        }
        ?>
      </div>
      <button type="button" class="add-btn" id="addNavBtn">+ 添加导航按钮</button>
    </div>

    <div class="card">
      <h2>分享按钮设置</h2>
      <div class="field"><label>分享标题（默认 MY SPACE）</label><input name="share_title" value="<?php echo htmlspecialchars(isset($data['share']['title']) ? $data['share']['title'] : 'MY SPACE'); ?>"></div>
      <div class="field"><label>分享文案（可用 {name} 自动替换为你的名字）</label><textarea name="share_text" rows="2"><?php echo htmlspecialchars(isset($data['share']['text']) ? $data['share']['text'] : ''); ?></textarea></div>
    </div>

    <div class="card">
      <h2>网址导航入口（增删改，拖动排序暂不支持）</h2>
      <div id="linkRows">
        <?php
        $idx = 0;
        foreach ($data['links'] as $lk) {
            $ck = color_key($lk['color']);
            $icon = isset($lk['icon']) && isset($ICONS[$lk['icon']]) ? $lk['icon'] : 'home';
            echo '<div class="link-row" data-idx="'.$idx.'">';
            echo '<div class="top"><b>入口 ' . ($idx + 1) . '</b><label class="del-label"><input type="checkbox" name="del[]" value="1" onchange="this.closest(\'.link-row\').classList.toggle(\'del\',this.checked)"> 删除此项</label></div>';
            echo '<div class="link-grid">';
            echo '<div class="field"><label>名称（如：抖音）</label><input name="n[]" value="'.htmlspecialchars($lk['name']).'"></div>';
            echo '<div class="field"><label>副标题</label><input name="s[]" value="'.htmlspecialchars($lk['sub']).'"></div>';
            echo '<div class="field"><label>链接 URL</label><input name="u[]" value="'.htmlspecialchars($lk['url']).'" placeholder="https://..."></div>';
            echo '<div class="field"><label>分类</label><select name="c[]">';
            foreach ($CATS as $ct) echo '<option value="'.$ct.'"'.($lk['cat']===$ct?' selected':'').'>'.$ct.'</option>';
            echo '</select></div>';
            echo '<div class="field"><label>颜色</label><select name="co[]">';
            foreach ($COLORS as $k => $cn) echo '<option value="'.$k.'"'.($ck===$k?' selected':'').'>'.$cn.'</option>';
            echo '</select></div>';
            echo '<div class="field"><label>图标</label><select name="i[]">';
            foreach ($ICONS as $k => $cn) echo '<option value="'.$k.'"'.($icon===$k?' selected':'').'>'.$cn.'</option>';
            echo '</select></div>';
            echo '</div></div>';
            $idx++;
        }
        ?>
      </div>
      <button type="button" class="add-btn" id="addBtn">+ 添加入口</button>
    </div>

    <div class="card">
      <h2>修改管理密码</h2>
      <div class="field"><label>新密码（留空则不修改，至少 4 位）</label><input type="text" name="new_password" value="" placeholder="不修改请留空" autocomplete="off"></div>
      <div class="hint">当前默认密码为 admin888。请设置一个你自己记得住的密码；后台地址为 /admin.php，建议收藏。</div>
    </div>

    <button type="submit" class="save-btn">保 存 并 生 效</button>
  </form>
</div>

<script>
function rowHtml(idx) {
  var cats = <?php echo json_encode($CATS); ?>.map(function(c){return '<option value="'+c+'">'+c+'</option>';}).join('');
  var cols = <?php echo json_encode($COLORS); ?>.map(function(v,k){return '<option value="'+k+'">'+v+'</option>';}).join('');
  var ics = <?php echo json_encode($ICONS); ?>.map(function(v,k){return '<option value="'+k+'">'+v+'</option>';}).join('');
  return '<div class="link-row" data-idx="'+idx+'">' +
    '<div class="top"><b>入口 '+(idx+1)+'</b><label class="del-label"><input type="checkbox" name="del[]" value="1" onchange="this.closest(\'.link-row\').classList.toggle(\'del\',this.checked)"> 删除此项</label></div>' +
    '<div class="link-grid">' +
    '<div class="field"><label>名称（如：抖音）</label><input name="n[]" value=""></div>' +
    '<div class="field"><label>副标题</label><input name="s[]" value=""></div>' +
    '<div class="field"><label>链接 URL</label><input name="u[]" value="" placeholder="https://..."></div>' +
    '<div class="field"><label>分类</label><select name="c[]">'+cats+'</select></div>' +
    '<div class="field"><label>颜色</label><select name="co[]">'+cols+'</select></div>' +
    '<div class="field"><label>图标</label><select name="i[]">'+ics+'</select></div>' +
    '</div></div>';
}
function navRowHtml(idx) {
  var acts = {"top":"回顶部","msg":"弹提示","url":"跳转网址","tel":"拨打电话","mailto":"发送邮件"};
  var ics = {"home":"房子","link":"链接","upload":"上传","call":"电话","mail":"邮箱","user":"用户","star":"星形","heart":"爱心","doc":"文档","cam":"相机"};
  var ao = Object.keys(acts).map(function(k){return '<option value="'+k+'">'+acts[k]+'</option>';}).join('');
  var io = Object.keys(ics).map(function(k){return '<option value="'+k+'">'+ics[k]+'</option>';}).join('');
  return '<div class="link-row" data-idx="'+idx+'">' +
    '<div class="top"><b>导航 '+(idx+1)+'</b><label class="del-label"><input type="checkbox" name="ndel[]" value="1" onchange="this.closest(\'.link-row\').classList.toggle(\'del\',this.checked)"> 删除此项</label></div>' +
    '<div class="link-grid">' +
    '<div class="field"><label>文字</label><input name="nn[]" value=""></div>' +
    '<div class="field"><label>图标</label><select name="ni[]">'+io+'</select></div>' +
    '<div class="field"><label>点击动作</label><select name="na[]">'+ao+'</select></div>' +
    '<div class="field"><label>内容（提示语/网址/号码）</label><input name="nv[]" value=""></div>' +
    '</div></div>';
}
var next = document.querySelectorAll('.link-row').length;
document.getElementById('addBtn').addEventListener('click', function(){
  var box = document.getElementById('linkRows');
  box.insertAdjacentHTML('beforeend', rowHtml(next));
  next++;
});
document.getElementById('addNavBtn').addEventListener('click', function(){
  var box = document.getElementById('navRows');
  box.insertAdjacentHTML('beforeend', navRowHtml(box.children.length));
});
</script>
</body>
</html>
