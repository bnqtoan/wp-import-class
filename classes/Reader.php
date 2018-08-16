<?php
namespace Classes;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class MyReadFilter implements IReadFilter
{
	public function readCell($column, $row, $worksheetName = 'Simple')
	{
		if ($row >= 1 && $row <= 7) {
			if (in_array($column, range('A', 'E'))) {
				return true;
			}
		}
		return false;
	}
}

class Reader{
	static function readExcel($path){
		if (is_file($path))
		{
			$filterSubset = new MyReadFilter();
			$reader = IOFactory::load($path);
			$sheet = $reader->getActiveSheet();
			//$data = $sheet->rangeToArray('A1:B1', NULL, true, true);
			$data = $sheet->toArray();
			print_r($data);
		}
	}
}