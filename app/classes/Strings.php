<?php
namespace App\classes;
// http://forum.wmonline.com.br/topic/188764-transformar-primeira-letra-de-cada-palavra-em-maiuscula/
// adaptada com mb_convert_case por @Adautonet
Class Strings
{
	public static function converteNomeParaUsuario($s, $e = array('da', 'das', 'de', 'do', 'dos', 'e'))
	{
		$array_names = explode(' ',$s);
		foreach($array_names as &$name_part){
			if(in_array($name_part,$e))
				$name_part = strtolower($name_part);
			else
				$name_part = mb_convert_case($name_part,MB_CASE_TITLE,"UTF-8");

		}
		unset($name_part);
		return implode(' ',$array_names);

	}
	/**
	 *
	 * https://gist.github.com/rafael-neri/ab3e58803a08cb4def059fce4e3c0e40
	 * Função de validar o CPF
	 * @param cpf - número fornecido pelo usuário
	 * @return valido ou não
	 *
	 */
	public static function validaCPF($cpf)
	 {
 
		// Verifica se um número foi informado
	if(empty($cpf)) {
		return false;
	}

	// Elimina possivel mascara
	$cpf = preg_replace("/[^0-9]/", "", $cpf);
	$cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
	
	// Verifica se o numero de digitos informados é igual a 11 
	if (strlen($cpf) != 11) {
		return false;
	}
	// Verifica se nenhuma das sequências invalidas abaixo 
	// foi digitada. Caso afirmativo, retorna falso
	else if ($cpf == '00000000000' || 
		$cpf == '11111111111' || 
		$cpf == '22222222222' || 
		$cpf == '33333333333' || 
		$cpf == '44444444444' || 
		$cpf == '55555555555' || 
		$cpf == '66666666666' || 
		$cpf == '77777777777' || 
		$cpf == '88888888888' || 
		$cpf == '99999999999') {
		return false;
	 // Calcula os digitos verificadores para verificar se o
	 // CPF é válido
	 } else {   
		
		for ($t = 9; $t < 11; $t++) {
			
			for ($d = 0, $c = 0; $c < $t; $c++) {
				$d += $cpf[$c] * (($t + 1) - $c);
			}
			$d = ((10 * $d) % 11) % 10;
			if ($cpf[$c] != $d) {
				return false;
			}
		}

		return true;
	}
	}


	/**
	* Mascara Universal para PHP
	* por: Rafael Clares: http://blog.clares.com.br/php-mascara-cnpj-cpf-data-e-qualquer-outra-coisa
	* baixado em 04/09/2017
	*
	* @param $val valor da string a ser convertida ex.: 1633721308
	* @param $mask: formato da string '(##) ####-####'
	* @return String formatada: (16) 3362-0580
	**/
	public static function mask($val, $mask)
	{
	 $maskared = '';
	 $k = 0;
	 for($i = 0; $i<=strlen($mask)-1; $i++)
	 {
		 if($mask[$i] == '#')
		 {
			 if(isset($val[$k]))
			 $maskared .= $val[$k++];
		 }
		 else
		 {
			 if(isset($mask[$i]))
			 $maskared .= $mask[$i];
		 }
	 }
	 return $maskared;
	}
	public static function formataTelefone($tel){
		$telefone=str_replace('-', '', $tel);
		$tipo=strlen($telefone);
		switch ($tipo) {
			case '8':
				//fixo sem DDD
				return substr($telefone, 0,4).'-'.substr($telefone, 4,4);
				break;
			case '9':
				//movel sem DDD
				return "9 ".substr($telefone, 1,4).'-'.substr($telefone, 5,4);
				break;
			case '10':
				//fixo com DDD
				return "(".substr($telefone, 0,2).") ".substr($telefone, 2,4).'-'.substr($telefone, 6,4);
				break;
			case '11':
				//movel com DDD
				return "(".substr($telefone, 0,2).") 9 ".substr($telefone, 3,4).'-'.substr($telefone, 7,4);
				break;
			
			default:
				return $telefone.'* verificar';
				break;
		}

	}

	/**
	 * Sanitiza strings
	 *
	 * - Remove tags HTML
	 * - Remove caracteres especiais não permitidos
	 * - Normaliza espaços
	 * - Coloca a primeira letra de cada palavra em maiúscula
	 *
	 * @param string $nome
	 * @return string
	 */
	public static function sanitizeField(string $field = ''): string
	{
		// Remove espaços no início e no fim
		$field = trim($field);

		// Remove tags HTML (protege contra XSS)
		$field = strip_tags($field);

		// Remove caracteres não permitidos
		// Permite letras, acentos, espaços, ponto, apóstrofo e hífen
		$field = preg_replace('/[^A-Za-zÀ-ÿ\s\.\'\-]/u', '', $field);

		// Converte múltiplos espaços em um único
		$field = preg_replace('/\s+/', ' ', $field);

		// Padroniza para "Title Case"
		$field = mb_convert_case($field, MB_CASE_TITLE, 'UTF-8');

		return $field;
	}

	/**
	 * Normaliza uma string de moeda para um formato padrão (ex: "1000.50").
	 * Lida com padrões BR (1.000,50) e US (1,000.50).
	 *
	 * @param string $valor A string de entrada do usuário.
	 * @return string O valor normalizado.
	 */
	public static function valorMonetario(string $valor): string
	{
		// 1. Remove qualquer coisa que não seja dígito, ponto ou vírgula
		$limpo = preg_replace('/[^\d,.]/', '', $valor);

		// 2. Encontra o último separador (ponto ou vírgula)
		$ultimoPonto = strrpos($limpo, '.');
		$ultimaVirgula = strrpos($limpo, ',');

		// 3. Verifica se ambos existem (ex: "1.000,50")
		if ($ultimoPonto !== false && $ultimaVirgula !== false) {
			
			// Se a vírgula vem por último (padrão BR: 1.000,50)
			if ($ultimaVirgula > $ultimoPonto) {
				$limpo = str_replace('.', '', $limpo); // Remove pontos (milhar)
				$limpo = str_replace(',', '.', $limpo); // Troca vírgula (decimal) por ponto
			}
			// Se o ponto vem por último (padrão US: 1,000.50)
			else {
				$limpo = str_replace(',', '', $limpo); // Remove vírgulas (milhar)
			}
		}
		// 4. Se só tem vírgula (ex: "10,50" ou "1,000" - ambíguo)
		elseif ($ultimaVirgula !== false) {
			// Se a vírgula está 3 casas antes do fim, provavelmente é milhar
			if ( (strlen($limpo) - $ultimaVirgula - 1 == 3) && substr_count($limpo, ',') == 1) {
				$limpo = str_replace(',', '', $limpo); // "1,000" -> "1000"
			} else {
				$limpo = str_replace(',', '.', $limpo); // "10,50" -> "10.50"
			}
		}
		// 5. Se só tem ponto (ex: "10.50" ou "1.000")
		elseif ($ultimoPonto !== false) {
			// Se o ponto está 3 casas antes do fim, provavelmente é milhar
			if ( (strlen($limpo) - $ultimoPonto - 1 == 3) && substr_count($limpo, '.') == 1) {
				$limpo = str_replace('.', '', $limpo); // "1.000" -> "1000"
			}
			// Se for "1.000.000", remove todos os pontos
			elseif (substr_count($limpo, '.') > 1) {
				$limpo = str_replace('.', '', $limpo); // "1.000.000" -> "1000000"
			}
			// Caso contrário (ex: "10.50"), já está correto e não faz nada.
		}

		return $limpo;
	}

}
