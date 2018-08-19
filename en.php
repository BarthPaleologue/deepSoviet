<!DOCTYPE HTML>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="robots" content="index, follow" />
    <meta http-equiv="Cache-control" content="public">
    <meta name="description" content="Your cat ? Your little brother ? This lamp ? Are they communists ? Find out with Deep Soviet !" />
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Deep Soviet - Online Communism Detector</title>
    <link rel="icon" href="deepSoviet.ico" />
    <link rel="stylesheet" href="css/style.css" />
    <script src="js/action-en.js"></script>

</head>

<body>
    <div id="loading-overlay">
        <p>Please wait while <span id="angerRed">deepSoviet</span><br/><span id="action"></span></p>

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
        <a href="index.php"><img src="french.png"/></a>
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
        <label for="img_selector" class="file-upload__label">Test Communism</label>
        <input id="img_selector" class="file-upload__input" type="file" name="file-upload">
    </div>

    <p id="version">deepSoviet<sub>v1.2.1</sub></p>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@0.12.5"></script>
    <script>
        $("body").css("height", $(window).height());

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
                    setTimeout(() => $("#image").css("margin-left", ($(window).width() - $("#image").width()) / 2), 10);
                    setTimeout(() => request(), 30);

                });
            }
            reader.readAsDataURL($("#img_selector")[0].files[0]);

            $("#UI").slideDown();
            $("#progressbar").animate({
                "width": "0%"
            }, 1);
            $("#rate").text("Calculating...");
        });

        function preprocess_image(imageData) {
            let offset = tf.scalar(127.5);
            return imageData.sub(offset).div(offset).expandDims(); // valeurs RGB entre -1 et 1
        }


        async function request() {

            let image = await $("#image").get(0);

            let tensor = await tf.fromPixels(image).resizeNearestNeighbor([224, 224]).toFloat();
            tensor = await preprocess_image(tensor);

            let predictions = await model.predict(tensor).data();
            console.log(predictions);

            let result = Math.round(predictions[0] * 100); // Taux de Communisme

            if (result == 100) {
                $("#status").text("Party Member !");
                earrape.play();
            } else if (result > 90) {
                $("#status").text("Good Soviet");
                anthem.play();
                anthem.volume = result / 100;
            } else if (result > 50) {
                anthem.play();
                anthem.volume = result / 100;
                $("#status").text("Sufficient Communism");
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