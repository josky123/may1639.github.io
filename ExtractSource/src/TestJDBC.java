import java.sql.*;
import java.util.List;

// Small test class for JDBC. Ignore.
public class TestJDBC 
{
	private static boolean debug = true;
	
	public static void main(String [] args) throws SQLException
	{
		// Load the driver
		try
		{
			// Load the driver (registers itself)
			Class.forName("com.mysql.jdbc.Driver");
		}
		catch (Exception e)
		{
			System.err.println("Unable to load driver");
			e.printStackTrace();
		}
		
		// Connect to the database
		Connection conn;
		
		String dbUrl = "jdbc:mysql://localhost/source";
		//String dbUrl = "jdbc:mysql://sdweb.ece.iastate.edu/may1639";
		//String user = "may1639";
		//String pass = "9nbje09p";
		String user = "root";
		String pass = "root";
		
		conn = DriverManager.getConnection(dbUrl, user, pass);
		
		System.out.println("***** Connected to database *****"); 
		
		// Drop table
		Statement drop = conn.createStatement();
		drop.executeUpdate("drop table repo");
		drop.close();
		
		// create a table
		Statement create = conn.createStatement();
		create.executeUpdate("create table repo (" +
							 "ID int not null," +
							 "Perms int," +
							 "Permnum int," +
							 "Snippet text," +
							 "Constraints text," +
							 "primary key (ID) )" 
							);
		create.close();
		System.out.println("***** Created Table *****");
		
		
		// Insert an entry into the table
		Statement insert = conn.createStatement();
		insert.executeUpdate(
								"insert into repo (ID, Perms, Permnum, Snippet, Constraints)" +
								"VALUES (0, 1, 2, \"Code Snippet\", \"Z3 Constraints\")"
							);
		insert.close();
		System.out.println("***** Inserted Row *****");
		
		// Display table
		Statement display = conn.createStatement();
		
		ResultSet rs = display.executeQuery( "select * from repo" );
		System.out.println("***** Table Contents *****");
		System.out.println("ID  Perms  Permnum  Snippet  Constraints");
		int id, perms, permnum;
		String snip, cons;
		while(rs.next())
		{
			id = rs.getInt("ID");
			perms = rs.getInt("Perms");
			permnum = rs.getInt("Permnum");
			snip = rs.getString("Snippet");
			cons = rs.getString("Constraints");
			System.out.println(id + ",   " + perms + ",     " + permnum + ",     " + snip + ", " + cons );
		}
		display.close();
		rs.close();
		conn.close();
		
	}
}
