<?php
namespace PHPSTORM_META
{

    use Kim1ne\B24\Application\Application;
    use Kim1ne\B24\Service\UserField\UserFieldService;

    registerArgumentsSet('bitrix_documentgenerator_serviceLocator_codes',
		Application::class,
		UserFieldService::class,
	);

	expectedArguments(\Bitrix\Main\DI\ServiceLocator::get(), 0, argumentsSet('bitrix_documentgenerator_serviceLocator_codes'));

	override(\Bitrix\Main\DI\ServiceLocator::get(0), map([
        Application::class => Application::class,
        UserFieldService::class => UserFieldService::class,
	]));
}
