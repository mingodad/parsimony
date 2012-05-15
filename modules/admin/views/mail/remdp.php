<html xmlns="http://www.w3.org/1999/xhtml">    <head>        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />        <style type="text/css">            /***********            2. The MailChimp Reset from Fabio Carneiro, MailChimp User Experience Design            More info and templates on Github: https://github.com/mailchimp/Email-Blueprints            http://www.mailchimp.com &amp; http://www.fabio-carneiro.com                        INLINE: Yes.            ***********/              /* Client-specific Styles */            #outlook a{padding:0;} /* Force Outlook to provide a "view in browser" button. */            body{width:100% !important;} .ReadMsgBody{width:100%;} .ExternalClass{width:100%;} /* Force Hotmail to display emails at full width */            body{-webkit-text-size-adjust:none; -ms-text-size-adjust:none;} /* Prevent Webkit and Windows Mobile platforms from changing default font sizes. */            /* Reset Styles */            body{margin:0; padding:0;}            img{height:auto; line-height:100%; outline:none; text-decoration:none;}            #backgroundTable{height:100% !important; margin:0; padding:0; width:100% !important;}            /** End Mail Chimp Reset **/             /** 3. Yahoo paragraph fix: removes the proper spacing or the paragraph (p) tag. To correct we set the top/bottom margin to 1em in the head of the document. Simple fix with little effect on other styling. You do not need to move this inline. NOTE: It is also common to use two breaks instead of the paragraph tag but I think this way is cleaner and more semantic. NOTE: This example recommends 1em or 1.12 em. More info on setting web defaults: http://www.w3.org/TR/CSS21/sample.html or http://meiert.com/en/blog/20070922/user-agent-style-sheets/                        INLINE: Yes.            **/            p {                margin: 1em 0;            }            /** 4. Hotmail header color reset: Hotmail replaces your header color styles with a green color on H2, H3, H4, H5, and H6 tags. In this example, the color is reset to black for a non-linked header, blue for a linked header, red for an active header (limited support), and purple for a visited header (limited support).  Replace with your choice of color. The !important is really what is overriding Hotmail's styling. Hotmail also sets the H1 and H2 tags to the same size.                         INLINE: Yes.            **/            h1, h2, h3, h4, h5, h6 {                color: black !important;                line-height: 100% !important;            }            h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {                color: blue !important;            }            h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {                color: red !important; /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */            }            h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {                color: purple !important; /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */            }            /** BONUS: Adaptation of Brian Thies (Campaign Monitor) link color fix to render the Yahoo Short Cuts invisible. Yahoo short cuts are links that Yahoo places over certain text in your email without your knowledge.  In order to use this fix you need to make the text color the same of the actual font color of your email and reset a few elements. IMPORTANT: You then need to use the Responsys/Smith Harmon link color fix (#4) if you want to style your links to the color you want them to be.  If you don't, this fix will change all links to black in Yahoo.                          If you are not worried about Yahoo's shorcuts, just remove this code.  This is not applicable for Yahoo Classic.                         INLINE: No.            **/            .yshortcuts, .yshortcuts a, .yshortcuts a:link,.yshortcuts a:visited, .yshortcuts a:hover, .yshortcuts a span { color: black; text-decoration: none !important; border-bottom: none !important; background: none !important;} /* Body text color for the New Yahoo.  This example sets the font of Yahoo's Shortcuts to black. */        </style>    </head>    <?php    if(!isset($imgpath)) $imgpath = 'http://parsimony.mobi/core/files/Parsimony.png';    if(!isset($companyName)) $companyName = 'Parsimony';    if(!isset($password)) $password = 'mdp';    if(!isset($name)) $name = 'Name FirstName';    if(!isset($adress)) $adress = 'Toulouse, France';    if(!isset($mail)) $mail = 'contact@test.com';    if(!isset($website)) $website = 'http://parsimony.mobi/';    if(!isset($presentation)) $presentation = 'Parsimony : A CMS of New Generation';    if(!isset($hello)) $hello =  utf8_decode('Hi ' . $name . ',');    if(!isset($helloText)) $helloText = 'Your profile has been created with success. You are on the right track to begin with Parsimony!';    if(!isset($login)) $login = 'contact@mail.com';    $unsuscribe = 'You\'re receiving this email as part of your Parsimony account. If you no longer wish to receive emails from Parsimony, you can unsubscribe.';    $mdp = 'You asked for a new password on Parsimony.<br>Your new password is : <b>' . $password . '</b>.';    $change = 'You can login to Parsimony and change it to something you\'ll remember.';    $team = '<b>The Parsimony team</b>';    ?>    <body>        <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#f2f2f2">            <tr>                <td valign="top" align="middle">                    <table width="551" border="0" cellspacing="0" cellpadding="0">                        <tr>                            <td align="left" bgcolor="#ffffff" style="BORDER-RIGHT: #9c9c9c 1px solid; PADDING-RIGHT: 5px; BORDER-TOP: #9c9c9c 1px solid; PADDING-LEFT: 5px; PADDING-BOTTOM: 5px; BORDER-LEFT: #9c9c9c 1px solid; PADDING-TOP: 5px; BORDER-BOTTOM: #9c9c9c 1px solid">                                <div style="BORDER-RIGHT: #424242 7px solid; BORDER-TOP: #424242 7px solid; BORDER-LEFT: #424242 7px solid; BORDER-BOTTOM: #424242 7px solid" >                                    <table width="525" border="0" cellspacing="0" cellpadding="0">                                        <tr>                                            <td style="PADDING-RIGHT: 20px; PADDING-LEFT: 20px; PADDING-BOTTOM: 20px; PADDING-TOP: 25px">                                                <table width="485" border="0" cellspacing="0" cellpadding="0">                                                    <tr>                                                        <td><img src="<?php echo $imgpath; ?>" alt="<?php echo $companyName; ?>" width="155" height="29"></td>                                                        <td align="right" valign="center" style="FONT-SIZE: 18px; COLOR: #acabab; FONT-FAMILY: Arial, Helvetica, sans-serif" ><?php echo date('l jS \of F Y '); ?></td>                                                    </tr>                                                </table>                                            </td>                                        </tr>                                        <tr>                                            <td>                                                <div style="BORDER-TOP: #d6d6d6 1px solid; PADDING-BOTTOM: 3px; PADDING-TOP: 3px; BORDER-BOTTOM: #d6d6d6 1px solid">                                                    <div style="BACKGROUND-COLOR: #424242">                                                        <table height="39" border="0" cellspacing="0" cellpadding="0">                                                            <tr>                                                                <td>                                                                    <A style="PADDING-RIGHT: 20px; PADDING-LEFT: 20px;COLOR: #e4e4e4;FONT-SIZE: 18px; PADDING-BOTTOM: 0px; PADDING-TOP: 15px; FONT-FAMILY: Arial, Helvetica, sans-serif"><?php echo $presentation; ?></A>                                                                </td>                                                            </tr>                                                        </table>                                                    </div>                                                </div>                                            </td>                                        </tr>                                        <tr>                                            <td style="PADDING-RIGHT: 20px; PADDING-LEFT: 20px; FONT-SIZE: 14px; PADDING-BOTTOM: 0px; COLOR: #333333; LINE-HEIGHT: 20px; PADDING-TOP: 30px; FONT-FAMILY: Arial, Helvetica, sans-serif">                                                <p style="FONT-SIZE: 18px; MARGIN: 0px 0px 10px; COLOR: #0165ab">                                                    <A style="COLOR: #0165ab"><u><?php echo $hello; ?></u></A>                                                </p>                                                <p style="MARGIN: 0px 0px 25px"><?php echo $mdp; ?>                                                 </p>                                                <p style="MARGIN: 0px 0px 25px"><?php echo $change; ?><br><br>                                                    <?php echo $team; ?>                                                             </p>                                                            </td>                                                            </tr>                                                            <tr>                                                                <td style="PADDING-RIGHT: 0px; BORDER-TOP: #d6d6d6 1px solid; PADDING-LEFT: 20px; FONT-SIZE: 12px;  PADDING-BOTTOM: 15px; COLOR: #666666; LINE-HEIGHT: 17px; PADDING-TOP: 15px; FONT-FAMILY: Arial, Helvetica, sans-serif; BACKGROUND-COLOR: #f6f6f6">                                                                    <b><?php echo $companyName; ?></b><br><?php echo $adress; ?><br>                                                                            <A style="COLOR: #4089bb" href="<?php echo $mail; ?>" target=_blank ><?php echo $mail; ?></A><br>                                                                                <a target="_blank" href="<?php echo $website; ?>" style="COLOR: #4089bb"><?php echo $website; ?></a>                                                                                <br><?php echo $unsuscribe; ?>                                                                                    </td>                                                                                    </tr>                                                                                    </table>                                                                                    </div>                                                                                    </td>                                                                                    </tr>                                                                                    </table>                                                                                    </td>                                                                                    </tr>                                                                                    </table>                                                                                    </body>                                                                                    </html>