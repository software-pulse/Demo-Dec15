<?php

include_once 'db_connect.php';

if (isset($_GET['campId'])) {

    $campId = $_GET['campId'];

    $cSQL = "select tempId,uploadStatus,groupId,campaignName,mediaUrl from whatsappCamp where campId = '$campId'";
    $cExe = $mysqli->query($cSQL);
    $cfetch = $cExe->fetch_assoc();


    $campaignName = $cfetch['campaignName'];
    $tempId = $cfetch['tempId'];
    $uploadStatus = $cfetch['uploadStatus'];
    $groupId = $cfetch['groupId'];

    if ($uploadStatus == 'LEAD') {


        $getNumSQL = "SELECT MSISDN,Id FROM whatsappLead where campId = '$campId'";
    } else if ($uploadStatus == 'GROUP') {


        $getNumSQL = "SELECT MSISDN,Id FROM whatsappLead where groupId = '$groupId'";
    }

    $sSQL = "SELECT * FROM whatsappTemp where wId = '$tempId'";
    $sExe = $mysqli->query($sSQL);
    $sfetch = $sExe->fetch_assoc();

    $tempName = $sfetch['tempName'];
    $btnType = $sfetch['btnType'];
    $btn1 = $sfetch['btn1'];
    $btn2 = $sfetch['btn2'];
    $btn3 = $sfetch['btn3'];
    $mediaType = $sfetch['mediaType'];
    $tempName = $sfetch['tempName'];
    $mediaUrl = $cfetch['mediaUrl'];
    $headerMedia = $sfetch['headerMedia'];
    $headerType = $sfetch['headerType'];
    $headerTxt = urlencode($sfetch['headerTxt']);
    $s3_URL = $sfetch['s3_URL'];
    $bodyContent = urlencode($sfetch['bodyContent']);
    $footerContent = urlencode($sfetch['footerContent']);
    // $btnType = $sfetch['btnType'];
    $btnCta = $sfetch['btnCta'];
    $btnCtaCpnTxt = urlencode($sfetch['btnCtaCpnTxt']);
    $btnCtaCpnCountry = $sfetch['btnCtaCpnCountry'];
    $btnCtaCpnNum = $sfetch['btnCtaCpnNum'];
    $btnCtaVwTxt = $sfetch['btnCtaVwTxt'];
    $btnCtaVwUrlType = $sfetch['btnCtaVwUrlType'];
    $btnCtaVwUrl = $sfetch['btnCtaVwUrl'];
    // $btn1 = $sfetch['btn1'];
    // $btn2 = $sfetch['btn2'];
    // $btn3 = $sfetch['btn3'];
    $currentDateTime = date('d/m/Y H:i:s');




    // print_r($getNumSQL);die;

    $getNumExe = mysqli_query($mysqli, $getNumSQL);
    while ($getNumFetch = mysqli_fetch_assoc($getNumExe)) {


        // The path to the file


        $Number = $getNumFetch['MSISDN'];
        $LeadId = $getNumFetch['Id'];



        if ($mediaType == 'N/A') {


            try {
            $custNum = substr($Number, -10);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://push.aclwhatsapp.com/pull-platform-receiver/wa/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{ 

        "messages": [ 
        { 
        "sender": "917092122221", 
        "to": "91' . $custNum . '", 
        "channel": "wa", 
        "type": "template", 
        "callbackDlrUrl": "http://chatdesk.pulsework360.com:8080/api/DLR",
        "template": { 
        "body": [], 
        "templateId": "' . $tempName . '", 
        "langCode": "en" 
        } 
        }

        ], 

        "responseType": "json" 

        } ',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'user: pulsepd',
                    'pass: pulsepd29'
                ),
            ));

            $response = curl_exec($curl);




            curl_close($curl);

            $data = json_decode($response, true);
            $transId = $data['responseId'];
            $currentTimestamp = time();
            $currentDateTime = date('d/m/Y H:i:s');
            $myfile = fopen("messagesend.txt", "a") or die("Unable to open file!");
            fwrite($myfile, $response);
            
            if ($response === false) {
                $curlErrorMessage = curl_error($curl);
                $errorLog = "curl_error_log.txt";
                $errorMessage = "cURL Error: " . $curlErrorMessage . PHP_EOL;
                file_put_contents($errorLog, $errorMessage, FILE_APPEND);
            }
   


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://whatsapp-dash.pulsework360.com:3007/get',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => '{

        "rqst_ack_id": "' . $transId . '",

        "del_time": "' . $currentDateTime . '",

        "mobile_no": "91' . $custNum . '",

        "del_status": "request",

        "cust_id":"917092122221",

        "type":"Txt",

        "campaignName":"' . $campaignName . '",

        "tempName":"' . $tempName . '"

        }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            echo $response = curl_exec($curl);
            


            curl_close($curl);

            $InDlrSQL = "Insert into whatsappDlr (bNum, MSISDN, ackId, delTime, delStatus) values ('917092122221', '91$custNum', '$transId', '$currentDateTime', 'request')";
            $InDlrExe = $mysqli->query($InDlrSQL);

            $InSQL = "update whatsappLead set status='1', responseId='$transId' where Id = '$LeadId'";
            $InExe = $mysqli->query($InSQL);
            /*{
                "id" : id,
                "src" : "917092122221",
                "dst" : $custNum,
                "timestamp" : $currentDateTime,
                "type" : "Templet",
                "msgType":"Outbound",
                headerType:$headerType,
                bodyContent:$bodyContent,
                footerContent:$footerContent
            }*/
            // $message['curlStatus'] = "Completed";

            if ($headerType == 'None') {
                $currentDateTime = date('Y-m-d H:i:s');
                $timestamp_unix = strtotime($currentDateTime);
                $temponenumber = 91 . $custNum;
                $urltemp = "http://$api_gateip:8080/api/$base_encrypt/insertoutboundtemplet?id=$transId&src=917092122221&dst=$temponenumber&timestamp=$timestamp_unix&type=Templet&msgType=Outbound&headerType=$headerType&bodyContent=$bodyContent&footerContent=$footerContent";
                if ($btnType == 'cta') {
                    if ($btnCta != 'N/A') {
                        $urltemp .= "&btnType=$btnType&btnCtaCpnTxt=$btnCtaCpnTxt&btnCtaCpnCountry=$btnCtaCpnCountry&btnCtaCpnNum=$btnCtaCpnNum";
                    }
                    if ($btnCtaVwTxt != 'N/A') {
                        $urltemp .= "&btnType=$btnType&btnCtaVwTxt=$btnCtaVwTxt&btnCtaVwUrl=$btnCtaVwUrl";
                    }
                } else if ($btnType == 'qr') {
                }
                $chtemp = curl_init();
                curl_setopt($chtemp, CURLOPT_URL, $urltemp);
                curl_setopt($chtemp, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($chtemp, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chtemp, CURLOPT_HEADER, 0);
                $datatemp = curl_exec($chtemp);
                echo $datatemp;

                curl_close($chtemp);
            } else if ($headerType == 'Text') {
                $currentDateTime = date('Y-m-d H:i:s');
                $timestamp_unix = strtotime($currentDateTime);
                $temponenumber = 91 . $custNum;
                $urltemp = "http://$api_gateip:8080/api/$base_encrypt/insertoutboundtemplet?id=$transId&src=917092122221&dst=$temponenumber&timestamp=$timestamp_unix&type=Templet&msgType=Outbound&headerType=$headerType&bodyContent=$bodyContent&footerContent=$footerContent&headerTxt=$headerTxt";
                if ($btnType == 'cta') {
                    if ($btnCta != 'N/A') {
                        $urltemp .= "&btnType=$btnType&btnCtaCpnTxt=$btnCtaCpnTxt&btnCtaCpnCountry=$btnCtaCpnCountry&btnCtaCpnNum=$btnCtaCpnNum";
                    }
                    if ($btnCtaVwTxt != 'N/A') {
                        $urltemp .= "&btnType=$btnType&btnCtaVwTxt=$btnCtaVwTxt&btnCtaVwUrl=$btnCtaVwUrl";
                    }
                } else if ($btnType == 'qr') {
                }
                $chtemp = curl_init();
                curl_setopt($chtemp, CURLOPT_URL, $urltemp);
                curl_setopt($chtemp, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($chtemp, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chtemp, CURLOPT_HEADER, 0);
                $datatemp = curl_exec($chtemp);
                echo $datatemp;
                $file = 'example.txt';
                // Write to the file
                file_put_contents($file, $timestamp_unix);
                curl_close($chtemp);
            }

        }catch (Exception $e) {
            // Handle the exception
            $errorMessage = "Error: " . $e->getMessage();
            
            // Log the error to a file
            $logFilename = "error_log.txt";
            file_put_contents($logFilename, $errorMessage . PHP_EOL, FILE_APPEND);}
        } else {


            $fileExtension = strtoupper($headerMedia);

            if ($mediaType == 'IMG') {

                if ($fileExtension == 'PNG' or $fileExtension == 'JPEG') {


                    $contentType = "image/$headerMedia";
                } else {

                    $message['fileType'] = "Invalid File Format";
                }
            } else if ($mediaType == 'VIDEO') {


                if ($fileExtension == 'MP4' or $fileExtension == '3GPP') {


                    $contentType = "video/$headerMedia";
                } else {


                    $message['fileType'] = "Invalid File Format";
                }
            } else if ($mediaType == 'DOC') {


                if ($fileExtension == 'PDF' or $fileExtension == 'DOCX' or $fileExtension == 'XLSX' or $fileExtension == 'XLS' or $fileExtension == 'CSV' or $fileExtension == 'PPT') {

                    $contentType = "application/$headerMedia";
                } else {


                    $message['fileType'] = "Invalid File Format";
                }
            }



            $custNum = substr($Number, -10);


            echo '{ 
                "messages": [ 
                { 
                "sender": "917092122221", 
                "to": "91' . $custNum . '", 
                "channel": "wa", 
                "type": "mediaTemplate", 
                "callbackDlrUrl": "http://chatdesk.pulsework360.com:8080/api/DLR",
                "mediaTemplate": { 
                "mediaUrl": "' . $mediaUrl . '",  
                "filename": "' . $tempName . '",
                "contentType": "' . $contentType . '", 
                "template": "' . $tempName . '",  
                "langCode": "en"
                } 
                } 
                ], 
                "responseType": "json" 
                } ';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://push.aclwhatsapp.com/pull-platform-receiver/wa/messages',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{ 
        "messages": [ 
        { 
        "sender": "917092122221", 
        "to": "91' . $custNum . '", 
        "channel": "wa", 
        "type": "mediaTemplate", 
        "mediaTemplate": { 
        "mediaUrl": "' . $mediaUrl . '",  
        "filename": "' . $tempName . '",
        "contentType": "' . $contentType . '", 
        "template": "' . $tempName . '",  
        "langCode": "en"
        } 
        } 
        ], 
        "responseType": "json" 
        } 
        ',
                CURLOPT_HTTPHEADER => array(
                    'user: pulsepd',
                    'pass: pulsepd29',
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $data = json_decode($response, true);
            $transId = $data['responseId'];
            $currentTimestamp = time();
            $currentDateTime = date('d/m/Y H:i:s');


            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://whatsapp-dash.pulsework360.com:3007/get',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_POSTFIELDS => '{

        "rqst_ack_id": "' . $transId . '",

        "del_time": "' . $currentDateTime . '",

        "mobile_no": "91' . $custNum . '",

        "del_status": "request",

        "cust_id":"917092122221",

        "type":"' . $contentType . '",

        "campaignName":"' . $campaignName . '",

        "tempName":"' . $tempName . '"

        }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            echo $response = curl_exec($curl);

            curl_close($curl);


            $InDlrSQL = "Insert into whatsappDlr (bNum, MSISDN, ackId, delTime, delStatus) values ('917092122221', '91$custNum', '$transId', '$currentDateTime', 'request')";
            $InDlrExe = $mysqli->query($InDlrSQL);

            $InSQL = "update whatsappLead set status='1', responseId='$transId' where Id = '$LeadId'";
            $InExe = $mysqli->query($InSQL);

            if ($headerType == 'Media') {
                // if ($mediaType == 'VIDEO') {
                $currentDateTime = date('Y-m-d H:i:s');
                $timestamp_unix = strtotime($currentDateTime);
                $temponenumber = 91 . $custNum;
                $urltemp = "http://$api_gateip:8080/api/$base_encrypt/insertoutboundtemplet?id=$transId&src=917092122221&dst=$temponenumber&timestamp=$timestamp_unix&type=Templet&msgType=Outbound&headerType=$headerType&bodyContent=$bodyContent&footerContent=$footerContent&mediaType=$mediaType&s3_URL=$mediaUrl";
                if ($btnType == 'cta') {
                    $urltemp .= "&btnType=$btnType";
                    if ($btnCta != 'N/A') {
                        $urltemp .= "&btnCtaCpnTxt=$btnCtaCpnTxt&btnCtaCpnCountry=$btnCtaCpnCountry&btnCtaCpnNum=$btnCtaCpnNum";
                    }
                    if ($btnCtaVwTxt != 'N/A') {
                        $urltemp .= "&btnCtaVwTxt=$btnCtaVwTxt&btnCtaVwUrl=$btnCtaVwUrl";
                    }
                } else if ($btnType == 'qr') {
                    $urltemp .= "&btnType=$btnType";
                    if ($btn1 != 'N/A') {
                        $urltemp .= "&btn1=$btn1";
                    }
                    if ($btn2 != 'N/A') {
                        $urltemp .= "&btn2=$btn2";
                    }
                    if ($btn3 != 'N/A') {
                        $urltemp .= "&btn3=$btn3";
                    }
                }
                $chtemp = curl_init();
                curl_setopt($chtemp, CURLOPT_URL, $urltemp);
                curl_setopt($chtemp, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($chtemp, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($chtemp, CURLOPT_HEADER, 0);
                $datatemp = curl_exec($chtemp);
                echo $datatemp;
                $file = 'example.json';
                // Write to the file
                file_put_contents($file, $currentDateTimess);
                curl_close($chtemp);
                // }else if($mediaType == 'IMG'){

                // }
            }




            // echo $message['curlStatus'] = "Completed";


        }

    
    
    }
}
