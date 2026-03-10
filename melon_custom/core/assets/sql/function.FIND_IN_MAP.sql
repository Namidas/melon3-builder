CREATE FUNCTION IF NOT EXISTS FIND_IN_MAP(needle INT,haystack VARCHAR(255))
RETURNS BOOLEAN
BEGIN
	DECLARE search_result BOOLEAN DEFAULT false;
	DECLARE regex_string VARCHAR(255) DEFAULT CONCAT('[:,]',needle,'[:,]');
	IF FIND_IN_SET(needle,haystack) <> 0 OR REGEXP_INSTR(haystack,regex_string) <> 0 THEN
		SET search_result = true;
	END IF;
	RETURN search_result;
END