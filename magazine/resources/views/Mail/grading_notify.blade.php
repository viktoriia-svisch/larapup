<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Grading inform</title>
    <style>
        .divBlock {
            display: block;
            max-width: 800px;
            margin: auto;
        }
        .divBlock > * {
            margin: 1rem 0;
            color: black;
        }
        .activeButton {
            display: flex;
            padding: 1rem 3rem;
            width: max-content;
            height: max-content;
            text-transform: capitalize;
            align-self: center;
            margin: 2rem auto;
            white-space: nowrap;
            background: #0288D1;
            color: white !important;
            justify-content: center;
            align-items: center;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="divBlock">
    <h2>Hi there, <b style="color: #0288D1;">{{$coordinator->name}}!</b></h2>
    <span>
        A student just submitted his/her article to your in-charge faculty. His article is waiting to be graded by you. You will have
        14 days <b>from the last deadline</b> to grading and choosing which article in your faculty to be published.
        <br>
        If after 14 days that student aren't graded, his score will be default set as 5 out of 10. Therefore, please remember to
        grading. You can grading right now by clicking the below button.
    </span>
    <br>
    <a href="{{getenv('APP_URL')}}/coordinator/faculty/{{$faculty_id}}/{{$semester_id}}/article" class="activeButton">
        Grading now
    </a>
    <br>
    <span>
        If you have any issues, please email to us, we will try our best to resolve.
    </span>
    <br>
    <h4><i>Sincerely, <br>University of Greenwich - Magazine Services</i></h4>
    <br>
    <hr>
</div>
</body>
</html>
