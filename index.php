<!DOCTYPE HTML>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow" />
    <meta http-equiv="Cache-control" content="public">
    <meta name="description" content="Ce chat ? Mon petit frère ? Cette lampe ? Sont-ils communistes ? Découvrez le avec deepSoviet !" />
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="robots" content="index, follow">
    <title>Deep Soviet - Détecteur de Communisme en Ligne</title>
    <link rel="icon" href="deepSoviet.ico" />
    <link rel="stylesheet" href="css/style.css" />
    <script src="js/actions.js"></script>

</head>

<body>
    <div id="loading-overlay">
        <p>Veuillez patienter pendant que <span id="angerRed">deepSoviet</span><br/><span id="action"></span></p>

        <div id="loader">
            <div id="r3">
                <div id="r2">
                    <div id="r1">
                        <div id="hal"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById("action").innerHTML = actions.getRandomIndex();
    </script>

    <h1>Deep Soviet</h1>

    <div id="flag">
        <a href="en.php"><img src="english.jpg"/></a>
    </div>

    <p id="status"></p>


    <div id="image-container">
        <img id="image" src="" />
    </div>

    <div id="UI">
        <div id="progressbar-container">
            <div id="progressbar"></div>
            <p id="rate">0%</p>
        </div>
    </div>


    <div class="file-upload">
        <label for="img_selector" class="file-upload__label">Tester le Communisme</label>
        <input id="img_selector" class="file-upload__input" type="file" name="file-upload">
    </div>

    <p id="version">deepSoviet<sub>v1.2.2</sub></p>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@0.12.5"></script>
    <script>
        $("body").css("height", $(window).height());


        function imageToDataURL(img, width, height) {

            // create an off-screen canvas
            var canvas = document.createElement('canvas'),
                ctx = canvas.getContext('2d');

            // set its dimension to target size
            canvas.width = width;
            canvas.height = height;

            // draw source image into the off-screen canvas:
            ctx.drawImage(img, 0, 0, width, height);

            // encode image to data-uri with base64 version of compressed image
            return canvas;
        }

        const anthem = new Audio('anthems/anthem.mp3');
        const earrape = new Audio('anthems/earrape.mp3');
        const goulag = new Audio('anthems/metal.mp3');

        anthem.load();
        earrape.load();
        goulag.load();

        var model;
        (async function getModel() {
            model = await tf.loadModel("v<?php if(isset($_GET["v"])) echo($_GET["v"]); else echo("1.2.1");?>/model.json");
            $("#loading-overlay").fadeOut();
        })();

        var image;
        $("#img_selector").on("change", e => {
            $(".file-upload").addClass("down");
            $("#status").fadeOut(0);

            earrape.load();
            anthem.load();
            goulag.load();

            let reader = new FileReader();
            reader.onload = e => {
                $("#image").attr("src", reader.result);
                $("#image").fadeIn(100, () => {
                    $("#image").css({
                        "margin-left": ($(window).width() - $("#image").width()) / 2
                    }, setTimeout(() => request(), 100));
                });
            }
            reader.readAsDataURL($("#img_selector")[0].files[0]);

            $("#UI").slideDown();
            $("#progressbar").animate({
                "width": "0%"
            }, 1);
            $("#rate").text("Analyse...");
        });

        function preprocess_image(imageData) {
            let offset = tf.scalar(127.5);
            return imageData.sub(offset).div(offset).expandDims(); // valeurs RGB entre -1 et 1
        }


        async function request() {

            let image = await imageToDataURL($("#image").get(0),224,224);

            let tensor = await tf.fromPixels(image).resizeNearestNeighbor([224, 224]).toFloat();
            tensor = await preprocess_image(tensor);

            let predictions = await model.predict(tensor).data();
            console.log(predictions);

            let result = Math.round(predictions[0] * 100); // Taux de Communisme

            if (result == 100) {
                $("#status").text("Membre du Parti !");
                earrape.play();
            } else if (result > 90) {
                $("#status").text("Soviet de Qualité");
                anthem.play();
                anthem.volume = result / 100;
            } else if (result > 50) {
                anthem.play();
                anthem.volume = result / 100;
                $("#status").text("Communisme Acceptable");
            } else if (result <= 50) {
                $("#status").text("GOULAG !");
                goulag.play();
            }

            $("#progressbar").animate({
                "width": result + "%"
            }, 1000);
            for (let i = 0; i <= result; i++) {
                setTimeout(() => $("#rate").text(i + "%"), 1000 * i / result);
            }
            setTimeout(() => $("#status").fadeIn(0), 1000);
        }
    </script>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-91827071-2"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'UA-91827071-2');
    </script>

</body>



</html>