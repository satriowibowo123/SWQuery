# SWQuery #

The Library to executes SQL commands with easy steps.

Example usage:
```
$conn = new SWQuery( 'localhost', 'root', 'mypass', 'mydatabase' );
$conn->query( 'get_list', 'SELECT * FROM list' );
$res = $conn->result_array( 'get_list' );

foreach ( $res as $val ) {
    echo $val['id'];
    echo $val['name'];
}
```
