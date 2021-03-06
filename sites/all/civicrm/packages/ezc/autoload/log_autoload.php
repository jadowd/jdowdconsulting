<?php
/**
 * Autoloader definition for the EventLog component.
 *
 * @copyright Copyright (C) 2005-2007 eZ systems as. All rights reserved.
 * @license http://ez.no/licenses/new_bsd New BSD License
 * @version 1.1
 * @filesource
 * @package EventLog
 */

return array(
    'ezcLogWriterException' => 'EventLog/exceptions/writer_exception.php',
    'ezcLogWriter'          => 'EventLog/interfaces/writer.php',
    'ezcLogFileWriter'      => 'EventLog/writers/writer_file.php',
    'ezcLogMapper'          => 'EventLog/interfaces/mapper.php',
    'ezcLog'                => 'EventLog/log.php',
    'ezcLogContext'         => 'EventLog/context.php',
    'ezcLogFilter'          => 'EventLog/structs/log_filter.php',
    'ezcLogFilterRule'      => 'EventLog/mapper/filter_rule.php',
    'ezcLogFilterSet'       => 'EventLog/mapper/filterset.php',
    'ezcLogMessage'         => 'EventLog/log_message.php',
    'ezcLogUnixFileWriter'  => 'EventLog/writers/writer_unix_file.php',
);
?>
