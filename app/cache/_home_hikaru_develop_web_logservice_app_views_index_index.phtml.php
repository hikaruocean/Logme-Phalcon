<!DOCTYPE html>
<html>

    <head>

        <meta charset="UTF-8">

        <title>Logme - Login</title>

        <link rel="stylesheet" href="/css/style.css">

    </head>

    <body>

    <html lang="en-US">
        <head>

            <meta charset="utf-8">

            <title>Login</title>

            <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400,700">

            <!--[if lt IE 9]>
          <script src="/js/html5.js"></script>
         <![endif]-->
            <script type="text/javascript" src="/js/jquery-1.11.2.min.js"></script>
            <script type="text/javascript" src="/js/jquery.form.js"></script>
            <script type="text/javascript">
                $('#login form').ajaxForm({
                    dataType:'json',
                    success:function(json){
                        if(json.result === '200'){
                            location.href = '/admin/';
                        }
                        else{
                            alert(json.msg);
                        }
                    }
                });
            </script>

        </head>

        <body>

            <div class="container">

                <div id="login">

                    <form action="/login" method="POST">

                        <fieldset class="clearfix">

                            <p><span class="fontawesome-user"></span><input type="text" name="id" value="Username" onBlur="if (this.value == '')
                        this.value = 'Username'" onFocus="if (this.value == 'Username')
                                    this.value = ''" required></p> <!-- JS because of IE support; better: placeholder="Username" -->
                            <p><span class="fontawesome-lock"></span><input type="password" name="password"  value="Password" onBlur="if (this.value == '')
                        this.value = 'Password'" onFocus="if (this.value == 'Password')
                                    this.value = ''" required></p> <!-- JS because of IE support; better: placeholder="Password" -->
                            <p><input type="submit" value="Sign In"></p>

                        </fieldset>

                    </form>

                    <p>Not a member? <a href="#">Sign up now</a><span class="fontawesome-arrow-right"></span></p>

                </div> <!-- end login -->

            </div>

        </body>
    </html>

</body>

</html>