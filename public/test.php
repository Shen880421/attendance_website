<?php
echo "<h1>PHP 測試頁面</h1>";
echo "<p>PHP 版本: " . PHP_VERSION . "</p>";
echo "<p>當前時間: " . date('Y-m-d H:i:s') . "</p>";

// 測試 autoload - 修正路徑
if (file_exists('../vendor/autoload.php')) {
    echo "<p>✅ Composer autoload 找到</p>";
    require_once '../vendor/autoload.php';

    // 測試 Twig 類別是否存在
    if (class_exists('Twig\Environment')) {
        echo "<p>✅ Twig Environment 類別可使用</p>";
    } else {
        echo "<p>❌ Twig Environment 類別不存在</p>";
    }
} else {
    echo "<p>❌ Composer autoload 不存在</p>";
}
echo "<h2>測試</h2>";
// 測試資料庫連線 - 修正路徑


// 測試 Twig 模板 - 修正路徑
echo "<h2>Twig 模板測試</h2>";
try {
    require_once '../inc/twig.inc.php';
    echo "<p>✅ Twig 設定檔載入成功</p>";
} catch (Exception $e) {
    echo "<p>❌ Twig 設定檔載入失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 渲染
try {
    $template = $twig->load('test.twig');
    echo "<p>✅ Twig 模板載入成功</p>";
    echo $template->render(['message' => '這是 Twig 渲染的測試訊息！']);
} catch (Exception $e) {
    echo "<p>❌ Twig 模板渲染失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 擴展
try {
    $twig->addExtension(new \Twig\Extension\DebugExtension());
    echo "<p>✅ Twig Debug 擴展已添加</p>";
} catch (Exception $e) {
    echo "<p>❌ 添加 Twig Debug 擴展失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 全域變數
try {
    $twig->addGlobal('globalVar', '這是全域變數');
    echo "<p>✅ Twig 全域變數已添加</p>";
} catch (Exception $e) {
    echo "<p>❌ 添加 Twig 全域變數失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 自訂函式
try {
    $twig->addFunction(new \Twig\TwigFunction('customFunction', function ($name) {
        return "Hello, $name!";
    }));
    echo "<p>✅ Twig 自訂函式已添加</p>";
} catch (Exception $e) {
    echo "<p>❌ 添加 Twig 自訂函式失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 自訂過濾器
try {
    $twig->addFilter(new \Twig\TwigFilter('customFilter', function ($string) {
        return strtoupper($string);
    }));
    echo "<p>✅ Twig 自訂過濾器已添加</p>";
} catch (Exception $e) {
    echo "<p>❌ 添加 Twig 自訂過濾器失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 自訂測試
try {
    $twig->addTest(new \Twig\TwigTest('customTest', function ($value) {
        return is_string($value);
    }));
    echo "<p>✅ Twig 自訂測試已添加</p>";
} catch (Exception $e) {
    echo "<p>❌ 添加 Twig 自訂測試失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 自訂過濾器
try {
    $twig->addFilter(new \Twig\TwigFilter('reverse', function ($string) {
        return strrev($string);
    }));
    echo "<p>✅ Twig 自訂過濾器 'reverse' 已添加</p>";
} catch (Exception $e) {
    echo "<p>❌ 添加 Twig 自訂過濾器 'reverse' 失敗: " . $e->getMessage() . "</p>";
}
// 測試 Twig 自訂函式
try {
    $twig->addFunction(new \Twig\TwigFunction('greet', function ($name) {
        return "Hello, $name!";
    }));
    echo "<p>✅ Twig 自訂函式 'greet' 已添加</p>";
} catch (Exception $e) {
    echo "<p>❌ 添加 Twig 自訂函式 'greet' 失敗: " . $e->getMessage() . "</p>";
}
echo "<h2>資料庫測試</h2>";
try {
    require_once '../inc/db.inc.php';
    echo "<p>✅ 資料庫連線成功</p>";

    // 檢查資料庫是否有表格
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "<p>資料庫中的表格: " . implode(', ', $tables) . "</p>";
    } else {
        echo "<p>⚠️ 資料庫中沒有表格，需要執行初始化腳本</p>";
    }
} catch (Exception $e) {
    echo "<p>❌ 資料庫連線失敗: " . $e->getMessage() . "</p>";
}
