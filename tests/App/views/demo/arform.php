<h2>Form</h2>

<p>
    <a href="arform">新纪录</a>
</p>
<p>
    <a href="redirectUrl">编辑最新的记录</a>
</p>

<form method="post" id="form">
    账号：
    <input type="text" name="student[name]" value="<?= $student->name ?>">
    密码：
    <input type="text" name="student[password]" value="<?= $student->password ?>">
    年龄：
    <input type="text" name="student[age]" value="<?= $student->age ?>">
    出生年月：
    <input type="text" name="student[birthday]" value="<?= $student->birthday ?>">

    <input type="submit" id="btn">
</form>
<script>
    $(function() {
        $("#btn").click(function() {
            $.post('', $("#form").serialize(), function(r) {
                if (r.code == 200) {
                    alert('ok');
                    location.reload();
                } else {
                    var s = '';
                    var d = '';
                    for (var k in r.message) {
                        s += d + k + '：' + r.message[k];
                        d = "\n";
                    }
                    alert(s);
                }
            }, 'json');
            return false;
        });
    });
</script>