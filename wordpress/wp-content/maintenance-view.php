<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?= $title ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,900&display=swap&subset=latin-ext" rel="stylesheet">
        <style>
            html {
                font-size: 10px; }

            body {
                margin: 0;
                font-size: 1.6rem;
                font-family: 'Source Sans Pro', sans-serif;
                color: #8e918c;
                background: #dfdfdf; }

            html, body {
                height: 100%;
                width: 100%; }

            .container {
                align-items: center;
                display: flex;
                justify-content: center;
                height: 100%;
                width: 100%; }

            .content-maintenance {
                position: relative;
                top: -4rem; }
            .content-maintenance .bg {
                width: 530px;
                height: 87px;
                background: url("https://<?= get_domain() ?>/wp-content/maintenance-bg.jpg") center bottom no-repeat;
                margin-left: auto;
                margin-right: auto; }
            .content-maintenance .maintenance {
                background: white;
                width: 530px;
                text-align: center;
                margin-left: auto;
                margin-right: auto;
                border-radius: 4px; }
            .content-maintenance header {
                border-bottom: 1px solid #dfdfdf;
                padding: 3rem; }
            .content-maintenance h1 {
                color: #424242;
                font-size: 2.5rem;
                margin-top: 1rem;
                margin-bottom: 3rem; }
            .content-maintenance .main {
                padding: 3rem; }
            .content-maintenance .main p {
                font-weight: 600;
                line-height: 2.5rem; }
            .content-maintenance .logo img {
                max-width: 75%; }

            @media screen and (max-width: 575px) {
                body {
                    background: white; }
                .container {
                    align-items: flex-start; }
                .content-maintenance {
                    width: 100%;
                    position: static; }
                .content-maintenance .maintenance {
                    width: 100%; }
                .content-maintenance .bg {
                    display: none; }
                .content-maintenance .logo img {
                    width: 80%;
                    max-width: 230px; }
                .content-maintenance .main {
                    padding: 2rem 3rem; }
                .content-maintenance h1 {
                    font-size: 2.2rem; } }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content-maintenance">
                <div class="bg"></div>
                <div class="maintenance">
                    <header>
                        <div class="logo">
                            <img src="<?= $logo_url ?>" alt="logo" />
                        </div>
                    </header>
                    <div class="main">
                        <h1>
                            <?= $title ?>
                        </h1>
                        <div class="text">
                            <p>
                                <?= $content ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>