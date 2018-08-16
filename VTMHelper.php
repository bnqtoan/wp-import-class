<?php

class VTMHelper {
	static function isUrl( $url ) {
		return filter_var( $url, FILTER_VALIDATE_URL );
	}

	static function toCSV( $fileName, $data ) {
		# Generate CSV data from array
		$fh = fopen('php://temp', 'rw'); # don't create a file, attempt
		# to use memory instead

		# write out the headers
		fputcsv($fh, array_keys(current($data)), "\t");

		# write out the data
		foreach ( $data as $row ) {
			fputcsv($fh, $row, "\t");
		}
		rewind($fh);
		$csv = stream_get_contents($fh);
		fclose($fh);
		file_put_contents($fileName, $csv);
		return $csv;
	}
}