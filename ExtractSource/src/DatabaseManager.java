import java.sql.*;
import java.util.Iterator;
import java.util.List;

// Small test class for JDBC. Ignore.
public class DatabaseManager 
{
	private static boolean debug = true;
	private List<ParsedMethod> methods;
	private List<ParsedType> types;
	private Connection conn;
	
	public DatabaseManager(List<ParsedMethod> m, List<ParsedType> t)
	{
		methods = m;
		types = t;
	}
	
	/**
	 * Connects to a database
	 * @throws SQLException
	 */
	public void connect() throws SQLException
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
//		Connection conn;
		
		String dbUrl = "jdbc:mysql://localhost/source";
		//String dbUrl = "jdbc:mysql://sdweb.ece.iastate.edu/may1639";
		//String user = "may1639";
		//String pass = "9nbje09p";
		String user = "root";
		String pass = "root";
		
		conn = DriverManager.getConnection(dbUrl, user, pass);
		
		System.out.println("***** Connected to database *****\n");
	}
	
	/**
	 * Closes the current connection
	 * @throws SQLException
	 */
	public void close() throws SQLException {
		if (conn != null)
		{
			conn.close();
			System.out.println("***** Connection Closed *****\n");
		}
	}
	
	/**
	 * Creates and populates the Types table
	 * @throws SQLException
	 */
	public void createMethodsTable() throws SQLException
	{
		if(conn == null)
		{
			System.err.println("Error: Not connected to a database.");
			return;
		}
		// Drop table
		Statement drop = conn.createStatement();
		drop.executeUpdate("drop table methods");
		drop.close();

		
		// create table methods
		Statement create = conn.createStatement();
		create.executeUpdate("create table methods (" +
							 "ID int not null," +			//1
							 "Constructor boolean," +		//2
							 "Javadoc text," +				//3
							 "Annotations text," +			//4
							 "Modifiers text," +			//5
							 "TypeParams text," +			//6
							 "TypeParamBindings text," +	//7
							 "ReturnType text," +			//8
							 "Name text," +					//9
							 "Arguments text," +			//10
							 "NumArguments int," +			//11
							 "ArgumentTypes text," +		//12
							 "ThrownExceptions text," +		//13
							 "Body text," +					//14
							 "Source text," +				//15
							 "ContainingClass text," +		//16
							 "OuterClass text," +			//17
							 "primary key (ID) )" 
							);
		create.close();
		System.out.println("***** Created Table \"methods\" *****");
		
		// Insert an entry into the table
		PreparedStatement addMeth = conn.prepareStatement ("insert into methods (ID, Constructor, " +
														   "Javadoc, Annotations, Modifiers, TypeParams, " + 
														   "TypeParamBindings, ReturnType, Name, Arguments, " +
														   "NumArguments, ArgumentTypes, ThrownExceptions, " +
														   "Body, Source, ContainingClass, OuterClass)" + 
														   "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
														   );
		Iterator mIt = methods.iterator();
		ParsedMethod pm;
		int ct=0;
		while (mIt.hasNext())
		{
			pm = (ParsedMethod) mIt.next();
			
			addMeth.setInt    (1,  ct);
			addMeth.setBoolean(2,  pm.isConstructor());
			addMeth.setString (3,  pm.getJavadoc());
			addMeth.setString (4,  listToString(pm.getAnnotations()));
			addMeth.setString (5,  listToString(pm.getModifiers()));
			addMeth.setString (6,  listToString(pm.getTypeParameters()));
			addMeth.setString (7,  listToString(pm.getTypeParameterBindings()));
			addMeth.setString (8,  pm.getReturnType());
			addMeth.setString (9,  pm.getName());
			addMeth.setString (10, listToString(pm.getArguments()));
			addMeth.setInt    (11, pm.getNumArguments());
			addMeth.setString (12, listToString(pm.getArgumentTypes()));
			addMeth.setString (13, listToString(pm.getThrownExceptions()));
			addMeth.setString (14, pm.getBody());
			addMeth.setString (15, pm.getSource());
			addMeth.setString (16, pm.getContainingClass());
			addMeth.setString (17, pm.getOuterClass());
			
			addMeth.executeUpdate();
			System.out.println("***** Inserted Row " + ct + "*****");
			ct++;
		}
		addMeth.close();
		System.out.println("***** Table \"methods\" Populated *****\n");
	}
	
	/**
	 * Creates and populates the Types table
	 * @throws SQLException
	 */
	public void createTypesTable() throws SQLException
	{
		if(conn == null)
		{
			System.err.println("Error: Not connected to a database.");
			return;
		}
		// Drop table
		Statement drop = conn.createStatement();
		drop.executeUpdate("drop table types");
		drop.close();
		
		// create table methods
		Statement create = conn.createStatement();
		create.executeUpdate("create table types (" +
							 "ID int not null," +			//1
							 "IsInterface boolean," +		//2
							 "IsInnerClass boolean," +		//3
							 "Javadoc text," +				//4
							 "Annotations text," +			//5
							 "Modifiers text," +			//6
							 "Name text," +					//7
							 "TypeParams text," +			//8
							 "TypeParamBindings text," +	//9
							 "SuperClass text," +			//10
							 "Interfaces text," +			//11
							 "Source text," +				//12
							 "ContainingClass text," +		//13
							 "primary key (ID) )" 
							);
		create.close();
		System.out.println("***** Created Table \"types\" *****");
		
		// Insert an entry into the table
		PreparedStatement addType = conn.prepareStatement ("insert into types (ID, IsInterface, " +
														   "IsInnerClass, Javadoc, Annotations, Modifiers, " + 
														   "Name, TypeParams, TypeParamBindings, SuperClass, " +
														   "Interfaces, Source, ContainingClass)" +
														   "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
														   );
		Iterator typeIt = types.iterator();
		ParsedType pt;
		int ct=0;
		while (typeIt.hasNext())
		{
			pt = (ParsedType) typeIt.next();
			
			addType.setInt    (1,  ct);
			addType.setBoolean(2,  pt.isInterface());
			addType.setBoolean(3,  pt.isInnerClass());
			addType.setString (4,  pt.getJavadoc());
			addType.setString (5,  listToString(pt.getAnnotations()));
			addType.setString (6,  listToString(pt.getModifiers()));
			addType.setString (7,  pt.getName());
			addType.setString (8,  listToString(pt.getTypeParameters()));
			addType.setString (9,  listToString(pt.getTypeParameterBindings()));
			addType.setString (10, pt.getSuperClass());
			addType.setString (11, listToString(pt.getInterfaces()));
			addType.setString (12, pt.getSource());
			addType.setString (13, pt.getContainingClass());
			
			addType.executeUpdate();
			System.out.println("***** Inserted Row " + ct + "*****");
			ct++;
		}
		addType.close();
		System.out.println("***** Table \"types\" Populated *****\n");
	}
	
	public <T> String listToString(List<T> list)
	{
		StringBuilder sb = new StringBuilder();
		Iterator it = list.iterator();
		while (it.hasNext())
		{	T t = (T) it.next();
			if (List.class.isAssignableFrom(t.getClass()))
				listToString((List<T>) t);
			else
			{
				sb.append(t.toString());
				if (it.hasNext())
					sb.append(",");
			}
		}
		return sb.toString();
	}
}
