<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Upload</title>
    <style>
        body{
            font-family:'Roboto',sans-serif;
        }
        input{
            padding: 6px;
            margin-bottom: 5px;
            font-family:'Roboto',sans-serif;
        }
        #button{
  
            border-radius:15px;
            box-sizing: border-box;
            text-decoration:none;
            font-family:'Roboto',sans-serif;
            font-weight:300;
            color:#FFFFFF;
            background-color:#4eb5f1;
            text-align:center;
            transition: all 0.2s;
        }
        #button:hover{
            background-color: #4095c6;
        }
    </style>
</head>
<body>
    
<?php
//THIS FUNCTION TAKES CSV FILE AND PUTS ALL ROWS IN A MULTIDIMENSIONAL ARRAY
function csv_to_array($csv){
    $file = fopen($csv, 'r');
    $csv_rows=[];
    while (($line = fgetcsv($file)) !== FALSE) {
      //$line is an array of the csv elements
      array_push($csv_rows,$line);
    }
    fclose($file);
    
    return $csv_rows;
}


function login($url,$data){
    $fp = fopen("cookie.txt", "w");
    fclose($fp);
    $login = curl_init();
    curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($login, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($login, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($login, CURLOPT_TIMEOUT, 40000);
    curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($login, CURLOPT_URL, $url);
    curl_setopt($login, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($login, CURLOPT_POST, TRUE);
    curl_setopt($login, CURLOPT_POSTFIELDS, $data);
    ob_start();
    return curl_exec ($login);
    ob_end_clean();
    curl_close ($login);
    unset($login);    
}

function grab_page($site){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 40);
    curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($ch, CURLOPT_URL, $site);
    ob_start();
    return curl_exec ($ch);
    ob_end_clean();
    curl_close ($ch);
}



function publishProductsOLX($header, $csv_rows){
    $counter= 0;
    $result=[];
    $header_count= count($header);

    foreach($csv_rows as $row) {
        //COUNTER SKIPS THE FIRST ROW (CSV HEADER).
        $counter+=1;
        if($counter == 1) continue;
    
        if (($counter % 10) == 0) {
            sleep(5);
        }

        $product=[];

        $i=0;
        while($i < $header_count){
            $product[$header[$i]]= $row[$i];
            $i+=1;
        }

        //THIS IF STATEMENT CHECKS IF THE TITLE OF PRODUCT HAS MORE THAN 55 CHARCTERS.
        /*if(strlen($product['Title']) > 55){
            echo "Probili ste limit od 55 karaktera u naslovu prozvoda " . $product['Title'] . ". Ovaj proizvod nije objavljen. <br>";
            continue;
        }*/
    
        $price = preg_replace('/[a-zA-Z]+/', '', $product['Price']); //REMOVES ALL LETTERS FROM PRICE
        $price = preg_replace('/\s+/', '', $price);        //REMOVES SPACES IN PRICE
        $price = preg_replace('/\./', '', $price);         //REPLACES "," WITH "."
        $price = preg_replace('/,/', '.', $price);
        //echo $price;die;

        $img= curl_file_create($product['Pictures']);
        
        $formFields= array(
            'objavi_1' => 'da',
            'naziv_1' => $product['Title'],
            'cijena_1' => $price,
            'stanje_1' => 'novo',
            'slika_1' => $img,
            'opis_1' => $product['Description'] . "<h2>Uz svaki prodani proizvod uredno izdajemo ovjerenu garanciju i fiskalni račun!</h2>
<h2>Rok isporuke unutar 24 sata bilo gdje u BiH!
</h2>
<h2>Kontakt info:</h2>
<h2>Tel: 051/965-656</h2>
<h2>Mob: 066/164-771</h2><br><br><br>
<img src='https://alati.pro/olx_uslovi_kupovine.gif'> <br><br>Referenca: syn",
            'objavisve' => 'Objavi'
    
        );
        
        $url= $product['OLX category URL'];

        $fp = fopen("cookie.txt", "w");
        fclose($fp);
        $form = curl_init();

        curl_setopt($form, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($form, CURLOPT_COOKIEJAR, "cookie.txt");
        curl_setopt($form, CURLOPT_COOKIEFILE, "cookie.txt");
        curl_setopt($form, CURLOPT_TIMEOUT, 40000);
        curl_setopt($form, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($form, CURLOPT_URL, $url);
        curl_setopt($form, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($form, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($form, CURLOPT_POST, TRUE);
        curl_setopt($form, CURLOPT_POSTFIELDS, $formFields);
        ob_start();
        $result[]= curl_exec ($form);
        ob_end_clean();
        curl_close ($form);
        unset($form);
        
        
    }
    return $result;


}
function suggest_olx_category($product_title){
    $titleEncoded= rawurlencode($product_title);
    $link= 'https://www.olx.ba/objava/predloziKat?naziv=' . $titleEncoded;

    $reg='/prijedlog_pretraga.*?>(.*?)</';
    $data=file_get_contents($link);
    preg_match_all($reg, $data, $matches);

    $suggested_categories=[];
    
    $matches= $matches[1];
    //print_r($matches);//die;
    foreach ($matches as $match){
        $match=json_decode('"'.$match.'"');
        $match= $match . '<br>';

        $match= str_replace('&nbsp;','>',$match);
        $match= str_replace('&raquo;','',$match);
        print_r($match); //die;       

        // $reg='/&nbsp;\s*([a-zšđčćžA-ZŠĐČĆŽ|\/|\(|\)|\s]*)<br>/';
        //$reg='/([a-zšđčćžA-ZŠĐČĆŽ|\/|\(|\)|\s]*)<br>/';
        $reg='/> ([a-zšđčćžA-ZŠĐČĆŽ|\/|()|\s|&|;]+)<br>/';
        preg_match($reg, $match, $finalMatch);
       
        $cat= $finalMatch[1];
        $cat= trim($cat);

        //echo $cat . '<br>';
        array_push($suggested_categories, $cat);
    }
    //return $suggested_categories;
    $firstRecomendation = $suggested_categories[0];
    $firstRecomendation= preg_replace('/[(]+/', '\(', $firstRecomendation);
    $firstRecomendation= preg_replace('/[)]+/', '\)', $firstRecomendation);
    $firstRecomendation= preg_replace('/[\/]+/', '\/', $firstRecomendation);
    echo $firstRecomendation;

    $reg='/<a href="(.*?)" title="'. $firstRecomendation .'">/';
 
    //$data=file_get_contents('https://www.olx.ba/objava/brzaobjava');
    $page= grab_page('https://www.olx.ba/objava/brzaobjava');
    preg_match($reg, $page, $url);

    $url= 'https://www.olx.ba/' . $url[1];
    echo $url;

}


 
login("https://www.olx.ba/auth/login","username=office%40infoars.net&password=11ibHds3cd7izCG&zapamtime=on&csrf_token=LhJRlRvQv8wKjRTfOXu0C5zHunEvBB156x7MUWVk");

//suggest_olx_category(' Nosač za klimu 410&#215;410/3mm');die;

//POST PRODUCTS FROM CSV IF THE FILE IS UPLOADED
if (isset($_POST['submit'])){
    $uploaddir = 'uploads/';
    $uploadfile = $uploaddir . 'uploaded.csv';

    if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
        $csv_rows= csv_to_array($uploadfile);
        $header= $csv_rows[0];
        publishProductsOLX($header, $csv_rows);
        echo "File is valid, and was successfully uploaded.\n";
    } else {
        echo "File upload error!\n";
    }
}



?>
<div style="text-align: center;">
    <h1>Upload products to OLX</h1>
    <form enctype="multipart/form-data" action="olx.php" method="POST">
    <!--Enter category URL: <input type="text" name="cat-url"> <br>-->
        Send this CSV file: <input name="userfile" type="file" /> <br>
        <input type="submit" name='submit' value="Send File" id="button" />
    </form>
</div>


</body>
</html>


