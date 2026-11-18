<?php
/** @var string $content */
/** @var string $title */
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Сертификат') ?></title>
    <link rel="preload" href="<?= e(asset('fonts/manrope-cyrillic.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
    <style>
        body { background: #EFEAE3; margin: 0; padding: 24px; }
        .cert-toolbar { max-width: 1100px; margin: 0 auto 16px; display: flex; justify-content: space-between; gap: 12px; }
        .cert-sheet {
            max-width: 1100px; margin: 0 auto; background: #fff; border-radius: 18px;
            padding: 48px 56px; box-shadow: 0 20px 60px rgba(27,32,48,.12);
            border: 1px solid #E6DED3;
        }
        .cert-frame { border: 3px solid transparent; background: linear-gradient(#fff,#fff) padding-box,
            linear-gradient(118deg,#001F83,#BA0000) border-box; border-radius: 14px; padding: 44px 48px; }
        .cert-top { display: flex; justify-content: space-between; align-items: center; gap: 20px; }
        .cert-title { font-size: 34px; letter-spacing: .18em; text-transform: uppercase; color: #001F83; margin: 20px 0 6px; }
        .cert-sub { color: #6E7486; font-size: 13px; letter-spacing: .08em; text-transform: uppercase; }
        .cert-name { font-size: 40px; margin: 18px 0 10px; color: #1B2030; }
        .cert-text { font-size: 16px; color: #3A4152; line-height: 1.6; max-width: 780px; }
        .cert-stats { display: flex; gap: 34px; margin: 26px 0 10px; flex-wrap: wrap; }
        .cert-stat b { display: block; font-size: 26px; color: #001F83; }
        .cert-stat span { font-size: 12px; color: #6E7486; }
        .cert-bottom { display: flex; justify-content: space-between; align-items: flex-end; margin-top: 34px; gap: 24px; }
        .cert-sign { border-top: 1px solid #C9BFAF; padding-top: 8px; font-size: 13px; color: #6E7486; min-width: 240px; }
        .cert-number { font-size: 11px; color: #9A938A; text-align: right; }
        @page { size: A4 landscape; margin: 10mm; }
        @media print {
            body { background: #fff; padding: 0; }
            .cert-toolbar { display: none; }
            .cert-sheet { box-shadow: none; border: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
<?= $content ?>
</body>
</html>
