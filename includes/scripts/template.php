<?php
use Oli\Script;

$script = new Script();
$script->set('template', null);

if (empty($_['template']))
	$script->setErrorMessage('Missing "template" parameter.');
else
{
	$template = $_Oli->getTemplate($_['template']);
	$script->set('template', $template);

	if (empty($template))
		$script->setErrorMessage('Unknown or Invalid Template.');

	else if (@$_['htmlpreview'])
	{
		$_Oli->setContentType('HTML');
		echo $template;
		exit;
	}
}

$script->printJSON(['template'], true);
exit;
