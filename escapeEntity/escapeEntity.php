<?php
require './../common/DbMysql.class.php';
$dbConfig = include './dbConfig.php';
$dbModel = new DbMysql($dbConfig);
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $orgName = $_POST['orgName'];
    $insertSql = 'INSERT INTO escapeentity(orgname) VALUES("' . $orgName . '")';
    $result = $dbModel->execute($insertSql);
    exit();
}
if ($_POST) {
    $orgName = $_POST['orgName'];
    $insertSql = 'INSERT INTO escapeentity(orgname) VALUES("' . $orgName . '")';
    $result = $dbModel->execute($insertSql);
}
$selectSql = 'select orgname from escapeentity';
$orgList = $dbModel->select($selectSql);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <script src="./../common/jquery-1.7.2.min.js" type="text/javascript" ></script>
    </head>
    <body>
        <form action="" method="post">
            <input type="text" name="orgName" id="orgName" value="" />
            <input type="submit" value="提交" />
            <input type="button" value="ajax提交(3秒后自动刷新页面，检验添加结果)" onclick="ajaxSubmit();" />
        </form>
        <table>
            <tr>
                <td>
                    <a onclick="reload();">刷新页面</a>
                </td>
            </tr>
            <?php for ($i = 0; $i < count($orgList); $i++): ?>
                <tr>
                    <td style="border: solid #000 1px;">
                        <?php echo htmlspecialchars($orgList[$i]['orgname']); ?>&nbsp;
                    </td>
                </tr>
            <?php endfor; ?>
        </table>
        <script>
            var herf = window.location.href;
            var ajaxSubmit = function() {
                var orgName = $('#orgName').val();
                $.ajax({
                    type: 'post',
                    url: herf,
                    data: {
                        "orgName": orgName
                    },
                    dataType: 'json',
                    success: function() {
                        setTimeout('reload()',3000); //指定1秒刷新一次
                    }
                });
            }
            var reload = function() {
                window.location.replace(location.href);
            }
        </script>
    </body>
</html>
