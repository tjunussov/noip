<?php
        echo gmdate('Y-m-d H:i:s');
        $isLocal = false;

        $file = "currentip.txt";

        // внешний ip адрес
        $extip = file_get_contents('http://ipecho.net/plain');
        echo " extip [".$extip."] ";

        // достаем сохраненный ip адрес из файла
        // резолвим ip адрес из DNS No-Ip
        $ip = gethostbynameTJ('[HOST]');
        //$ip = file_get_contents($file);
        echo "currip [".$ip."] ";

        if($ip == [HOST]'){ // если DNS неможет найти имя meta4.noip.me
                $ip = file_get_contents($file);
                //telegram("DNS lookup failure - meta4.noip.me not found, trying from local file ");
                $isLocal = true;

                echo "fscurrip [".$ip."] ";
        }

        if($extip != $ip ){  // если внешний ip != сохранннему ip то, делаем измненение
                echo "changed \n";

                // дергаем сервис noip для изменения DNS
                $options  = array('http' => array('user_agent' => 'Linux PHP/0.1 admins@meta4.kz'));
                $context  = stream_context_create($options);
                $noipres = @file_get_contents("http://[USR]:[PWD]@dynupdate.no-ip.com/nic/update?hostname=[HOST]&myip=".$extip, false, $context);

                // пишем в telegram новый ip адрес
                telegram("IP address changed ".$ip.($isLocal?"L":"")." → ".$extip." noip says ".$noipres);

                // сохраняем в файл новый ip адрес
                file_put_contents($file, $extip);
        } else {
                // telegram("IP address is ".$extip);
                echo "unchanged \n";
        }


function telegram($m){
  @file_get_contents("https://api.telegram.org/bot[APIKEY]/sendMessage?chat_id=[CHATID]&text=".urlencode($m));
}


function gethostbynameTJ($host){
        $dns = 'nf1.no-ip.com';  // noip free dns
        $ip = `nslookup $host $dns`; // the backticks execute the command in the shell

        $ips = array();
        if(preg_match_all('/Address: ((?:\d{1,3}\.){3}\d{1,3})/', $ip, $match) > 0){
            $ips = $match[1];
        }

         if(count($ips) > 0) return @$ips[0];
        return $host;
}




 ?>
