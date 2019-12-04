<?php

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="author" content="root">
<meta name="robots" content="noindex">
<link rel="stylesheet" type="text/css" href="css/style.css">
<title>phpSANE: help</title>
</head>
<body>';


// Polish Help
// ============

$SCANIMAGE = "/usr/bin/scanimage";
$cmd = $SCANIMAGE . " -h";
$sane_help = `$cmd`;
unset($cmd);

$start = strpos($sane_help, "\nOptions specific to device") + 1;
if ($start !== FALSE)
{
  $sane_help = substr($sane_help, $start);
}

$start = strpos($sane_help, "\nType ``scanimage --help");
if ($start !== FALSE)
{
  $sane_help = substr($sane_help, 0, $start);
}

echo <<<EOT

<h1>
phpSANE: Aiuto
</h1>

<h3>
Area di scansione
</h3>

<p>
Scegliendo la il formato della pagina vengono impostate le dimensioni.
</p>

<p>
Cliccando sull'immagine di anteprima si possono impostare delle dimensioni personalizzate, spostando gli angoli che definiscono l'area.
</p>


<h3>
Opzioni di scansione
</h3>

<p>
Solo le principali opzioni di base sono supportate, 
che danno il controllo sulla qualità dell'immagine (--mode e --resolution)
 e il formato del file nel quale memorizzare l'immagine

</p>

<!--h4>
Extra
</h4>

<p>
Per il tuo scanner, ecco la lista completa delle opzioni (da <i>scanimage -h</i>):

</p>

<p>
<pre>
{$sane_help}
</pre>
</p>


<p>

Any extra options you want to add to the scan command,
you can add them in this 'extra' field.
</p>

<p>
NB. Invalid characters are replaced with an 'X'.
</p>

<p>
eg. To control the brightness :-
</p>

<p>
The value is not stanard across all scanners,
so you need to see what options your scanner takes.
It may be a percentage,
or a number in a range (eg. -4..3).
</p>

<p>
eg.<br>
--brightness=50%<br>
--brightness 2<br>
</p-->


<h3>
Pulsanti
</h3>

<h4>
Anteprima 
</h4>

<p>
Effettua una scansione di tutta l'area, a bassa risoluzione, e la mostra, per consentire di selezionare il riquadro desiderato.
</p>


<h4>
Scansiona
</h4>

<p>
Effettua una scansione dell'area selezionata e permette di salvare il file in output, in base al formato scelto.
</p>


<!--h4>
OCR :- (only available if 'gocr' is installed)
</h4>

<p>
Does a scan and uses OCR to convert the contents into an ASCII text file.
</p>

<p>
Recommend using Grayscale at 300dpi or above.
</p>


<h4>
Reset :-
</h4>

<p>
Re-loads the page,
but does nothing else.
</p>


<h4>
Clean :-
</h4>

<p>
Resets all parameters to their default values and clears the preview.
</p-->


<h3>
Comando di scansione
</h3>

<p>
Nella parte inferiore della pagina viene visualizzata la linea di comando eseguita per effettuare la scansione, facilitando la verifica e la risoluzione di eventuali problemi.
</p>

EOT;

?>

</body>
</html>

