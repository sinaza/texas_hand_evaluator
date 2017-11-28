<!DOCTYPE>
<html>
    <head>
        <script src="https://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.12.4.min.js"></script>
        <style>
            #container {
                text-align: center;
            }

            #action {
                float:left;
                margin-top: 25px;
                margin-left: 150px;
            }

            p {
                display: inline;
                margin-right: 15px;
                font-size: 17px;
            }

            button {
                margin-bottom: 30px;
                background-color: #000;
                color: #fff;
                width: 160px;
                height: 30px;
            }

            button:disabled {
                background-color: gray;
            }

            .notWin {
                color: grey;
            }

            .definiteWin {
                color: red;
            }

            .card {
                position: absolute;
            }

            .suit {
                margin-top: 25px;
            }

            .deck {
                padding-bottom: 110px;
                float:left;
            }

            .picked {
                padding-top: 20px;
            }

            .space {
                width: 15px;
                display: inline;
                padding: 15px;
            }
        </style>
    </head>
    <body>
        <div id="container">
        <div class="deck">
        <?php
            $suits = array('d', 'c', 'h', 's');
            
            foreach($suits as $suit)
            {
                echo "<div class=\"suit\">";
                for($i=1; $i<=13; $i++)
                {
                    echo "<div class='space'></div><img id={$suit}{$i} class='card' data-suit={$suit} data-num={$i} data-selected=0 width='70' src="."images/deck/{$suit}/{$i}.png"." onClick=changeVal(this) />";
                }

                echo "</div><br/>";
            }
        ?>
        </div>

        <div id="action">
        <button type="button" disabled="disabled"  onClick="sendHand()">Action</button>
        <div id="result"></div>
        </div>
        </div>
    </body>
    <script type="text/javascript">
        function changeVal(img)
        {
            canChange = true;
            imgObj = $(img);
            currentVal = imgObj.attr('data-selected');
            targetVal = (currentVal == '1') ? '0' : '1';

            //$(imgObj).data('selected', targetVal);
            if(targetVal == 1 && getHandLength() == 7)
                canChange = false;

            if(canChange)
            {
                $(imgObj).attr('data-selected', targetVal);

                if(targetVal == '1')
                    $(img).addClass('picked');
                else 
                    $(img).removeClass('picked');
            }

            changeOpacity();
            checkButton();
        }

        function getHand(json = true)
        {
            var arr = new Array();

            $('.card').each(function($index){
                if($($('.card')[$index]).attr('data-selected') == '1')
                {
                    var suit = $($('.card')[$index]).data('suit');
                    var num  = $($('.card')[$index]).data('num');

                    var obj = new Object();
                    obj[num] = suit;
                    arr.push(obj);
                }
            });

            return (json) ? JSON.stringify(arr) : arr;
        }

        function sendHand()
        {
            var hand = getHand();
            var result = '';
            var classResult;
            var counter = 0;

            changeOpacity(true);

            $.ajax({
                type: 'POST',
                url: 'action.php',
                data: {hand : hand},
                success: function(data) {
                    var obj = JSON.parse(data);

                    for(var prop in obj)
                    {
                        if(obj[prop])
                            classResult = 'definiteWin';
                        else
                            classResult = 'notWin';
                            
                        result = result + "<p class=\"" + classResult + "\">" + prop + "</p><br/>";
                    }

                    $("#result").html(result).hide().fadeIn(800);
                }
            });
        }

        function getHandLength()
        {
            return getHand(false).length;
        }

        function checkButton()
        {
            var handLength = getHandLength();

            if(handLength >=5 && handLength <=7)
                $("button").removeAttr("disabled");
            else
                $("button").attr("disabled", "disabled");
        }

        function changeOpacity(force = false)
        {
            if(getHandLength() == 7 || force)
            {
                $(".card.picked").css('filter', 'blur(0px)');
                $(".card.picked").css('z-index', '2');

                $(".card:not(.picked)").css('filter', 'blur(5px)');
                $(".card:not(.picked)").css('z-index', '0');
            }
            else
            {
                $(".card").css('filter', 'blur(0px)');
                $(".card").css('z-index', '2');
            }
        }
    </script>
</html>
