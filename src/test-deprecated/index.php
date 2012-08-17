<?php
/**
 *    Copyright 2012 MegaFon
 *
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *    limitations under the License.
 */

/**
 * index.php
 * 
 * @package index
 * @version $Id: index.php 1298 2012-08-07 17:42:00Z gsb $
 */
/**
 * index.php
 * 
 * @package index
 */
phpinfo();
exit();

ini_set('log_errors', 'on');
ini_set('error_log', 'php_errors.txt');

require_once('trunk/src/Histone.class.php');

/**
 * URIResolver
 * 
 * myUriResolver defines a concrete implementation of getting the template text
 * in a specific project. On the server side it can be a local file, an external resource,
 * Databases, etc.
 * 
 * @param string $resourceURI
 * @param string $baseURI
 * @param array $args optional parameters set
 * @return array
 * <ul>
 *      <li>    "uri": "the resulting resource uri by resolving $resourceURI relative to $baseURI"</li>
 *      <li>    "data": "content of the resource as a string"</li>
 * </ul>
 */
function myUriResolver($resourceURI, $baseURI, $args = null)
{
	$fileName = rtrim($baseURI, '/') . '/' . trim($href, '/');
	try
	{
		$template = file_get_contents($fileName);
		if ($template)
		{
			return $template;
		}
		return '';
	}
	catch (Exception $e)
	{
		return '';
	}
}

Histone::setUriResolver('myUriResolver');

try
{

	$templateStr = '{{var tr=array()}}{{for x in tr}}{{x}}12{{else}}77{{/for}} 2 * 2 = {{2 * (2 + 1)}}{{include ("tpl1.tpl")}}';
	$templateStr = "{{* значения используемые для тестирования *}}
{{var values = array(
    undefined,
    null,
    true,
    false,
    0,
    10,
    \"\",
    \"string\",
    array(),
    array(1),
    object(),
    object(foo: 'bar')
)}}
 
{{* операторы используемые для тестирования *}}
{{var operators = array('is', 'isNot', '>', '<', '>=', '<=')}}
{{for operator in operators}}
    {{for op1 in values}}
        {{for op2 in values}}
            <div>
                <strong>{{op1.toJSON()}} {{operator}} {{op2.toJSON()}}</strong>
                {{if operator is 'is'}}
                    <span> = {{(op1 is op2).toJSON()}}</span>
                {{elseif operator is 'isNot'}}
                    <span> = {{(op1 isNot op2).toJSON()}}</span>
                {{elseif operator is '>'}}
                    <span> = {{(op1 > op2).toJSON()}}</span>
                {{elseif operator is '<'}}
                    <span> = {{(op1 < op2).toJSON()}}</span>
                {{elseif operator is '>='}}
                    <span> = {{(op1 >= op2).toJSON()}}</span>
                {{elseif operator is '<='}}
                    <span> = {{(op1 <= op2).toJSON()}}</span>
                {{/if}}
            </div>
        {{/for}}
    {{/for}}
{{/for}}
";

	$template = new Histone('C:/work/Histone/php/templates/');
//	$template = new Histone('http://developer/php/templates/tpl1.tpl');
	$template->parseString($templateStr);
	$context = array(
		'var1' => 111,
		'var2' => 222,
	);
//	$context = (111);
//	$context = (string)json_decode(json_encode($context));
	echo $template->process($context);
}
catch (Exception $e)
{
	// При отладке выводим, на бою пишем в лог
	echo 'PHP says: ' . $e->getMessage() . '<table>' . $e->xdebug_message . '</table>';
}
?>