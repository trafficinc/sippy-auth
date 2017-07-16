<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Auth</title>
        <!--[if lte IE 8]>
        <script src="<?php echo site_url(); ?>assets/js/ie/html5shiv.js"></script>
        <![endif]-->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />

        <?php include(VIEWS_DIR . '/header.php'); ?>
    </head>
    <body>
        <div class="container">

            <ul class="nav nav-pills">
                    <li role="presentation"><a href="<?php echo site_url('auth/register'); ?>">Register</a></li>
                    <li role="presentation"><a href="<?php echo site_url('auth/login'); ?>">Login</a></li>
            </ul>

            <div class="col-md-12">

                <h1>Forgot Password</h1>

                <?php
                if (isset($messSuccess)) {
                    $this->success_block($messSuccess);
                }
                if (isset($errors)) {
                    $this->error_block($errors);
                }
                ?>

                <form method="post" action="<?php echo site_url('auth/forgot'); ?>">
                    <input type="hidden" name="csrf" value="<?php echo $this->security->generate_csrf_token(); ?>"/>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="Email" value="<?php echo (isset($email)) ? $email : ''; ?>">
                    </div>
                    <button type="submit" class="btn btn-default">Search</button>
                </form>

            </div>

            </div>  
            <?php include(VIEWS_DIR . '/footer.php'); ?>

        </div><!-- /.container -->


        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        
    </body>
</html>