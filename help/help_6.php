<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
<meta name="author" content="root">
<meta name="robots" content="noindex">
<link rel="stylesheet" type="text/css" href="css/style.css">
<title>phpSANE: help</title>
</head>
<body>

<?PHP

// English Help
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
phpSANE: Aide
</h1>

<h3>
Aire de num&eacute;risation
</h3>

<p>
Choisir le format pour fixer la dimension de la page.
</p>

<p>
Cliquer sur 'pr&eacute;visualisation' pour fixer les coins 
(haut gauche et bas droit) &agrave; la position de la souris.
</p>


<h3>
Options de num&eacute;risation
</h3>

<p>
Seules les options basiques sont support&eacute;es directement,
afin de contr&ocirc;ler la qualit&eacute; de l'image
(--mode et --resolution)
ainsi que le format du fichier &agrave; cr&eacute;er.
</p>

<h4>
Extra :-
</h4>

<p>
Pour votre scanner,
la liste compl&egrave; des options (&agrave; partir de : scanimage -h) est :-
</p>

<p>
<pre>
{$sane_help}
</pre>
</p>


<p>
Chaque option suppl&acute;mentaire que vous voulez utiliser
&agrave; la commande de num&eacute;risation peut &ecirc;tre 
ajout&eacute;e au champs 'extra'.
</p>

<p>
NB: Les caract&egrave;res invalides sont remplac&acute;s par 'X'.
</p>

<p>
ex: Pour contr&ocirc;ler la luminosit&eacute; :-
</p>

<p>
La valeur n'est pas standard selon les scanners,
vous devez donc vous aviser des options que votre scanner utilise.
Ce peut &ecirc;tre un pourcentage ou un nombre dans une plage (ex: -4..3).
</p>

<p>
ex:<br>
--brightness=50%<br>
--brightness 2<br>
</p>


<h3>
Boutons d'action
</h3>

<h4>
Pr&eacute;visualiser :-
</h4>

<p>
Fait une num&eacute;risation en basse r&eacute;solution 
de toute la page et vous donne un aper&ccedil;u qui permet
de s&eacute;lectionner l'aire &agrave; num&eacute;riser.
</p>


<h4>
Num&eacute;riser :-
</h4>

<p>
Lance la num&eacute;risation de l'aire s&eacute;lectionn&eacute;e 
sous le nom de fichier choisi (image ou text).
</p>


<h4>
ROC :- (si 'gocr' est install&eacute; uniquement)
</h4>

<p>
Num&eacute;rise et utilise la reconnaisance optique de caract&egrave;re (ROC)
pour convertir le contenu en fichier text ASCII.
</p>

<p>
Il est recommand&eacute; de num&eacute;riser en nuances de gris &agrave; 300 dpi (ppp).
</p>


<h4>
Recharger :-
</h4>

<p>
Recharge la page, un point c'est tout.
</p>


<h4>
R&eacute;initialiser :-
</h4>

<p>
Remet le formulaire aux valeurs par d&eacute;faut et vide la pr&eacute;visualisation.
</p>


<h3>
Commande de num&eacute;risation
</h3>

<p>
La ligne de commande utilis&eacute;e pour lancer la num&eacute;risation
est affich&eacute;e &agrave; la fin de la page.
Ceci vous permet de v&eacute;rifier le format de la commande
en cas d'erreurs.
</p>

EOT;

?>

</body>
</html>

