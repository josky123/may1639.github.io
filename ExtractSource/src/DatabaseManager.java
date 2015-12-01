import java.sql.*;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;

// Small test class for JDBC. Ignore.
public class DatabaseManager 
{
	// data to add to the database
	private HashMap<String, HashMap<String, ArrayList<SourceParser>>> data;
	// int to create unique id for entries.  Increment after use.
	
	private static boolean debug = true;
	private List<ParsedMethod> methods;
	private List<ParsedType> types;
	private Connection conn;
	private int batchMax = 1000;
	
	public DatabaseManager(List<ParsedMethod> m, List<ParsedType> t)
	{
		methods = m;
		types = t;
	}
	
	public DatabaseManager(HashMap<String, HashMap<String, ArrayList<SourceParser>>> data) {
		this.data = data;
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
		
//		//String dbUrl = "jdbc:mysql://sdweb.ece.iastate.edu/may1639_db";
//		String dbUrl = "jdbc:mysql://may1639.sd.ece.iastate.edu:622/may1639_db";
//		String user = "may1639";
//		String pass = "9nbje09p";
		String dbUrl = "jdbc:mysql://localhost:3306/source?rewriteBatchedStatements=true";
		String user = "root";
		String pass = "root";
		
		conn = DriverManager.getConnection(dbUrl, user, pass);
		conn.setAutoCommit(false);
		
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
	 * [DEPRECIATED] Creates and populates the Types table.
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
							 "DeclaringClass text," +		//16  change to declaring class
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
														   "Body, Source, DeclaringClass, OuterClass)" + 
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
			addMeth.setString (16, pm.getDeclaringClass());
			addMeth.setString (17, pm.getOuterClass());
			
			addMeth.executeUpdate();
			System.out.println("***** Inserted Row " + ct + "*****");
			ct++;
		}
		addMeth.close();
		System.out.println("***** Table \"methods\" Populated *****\n");
	}
	
	/**
	 * [DEPRECIATED] Creates and populates the Types table.
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
							 "DeclaringClass text," +		//13
							 "primary key (ID) )" 
							);
		create.close();
		System.out.println("***** Created Table \"types\" *****");
		
		// Insert an entry into the table
		PreparedStatement addType = conn.prepareStatement ("insert into types (ID, IsInterface, " +
														   "IsInnerClass, Javadoc, Annotations, Modifiers, " + 
														   "Name, TypeParams, TypeParamBindings, SuperClass, " +
														   "Interfaces, Source, DeclaringClass)" +
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
			addType.setString (13, pt.getDeclaringClass());
			
			addType.executeUpdate();
			System.out.println("***** Inserted Row " + ct + "*****");
			ct++;
		}
		addType.close();
		System.out.println("***** Table \"types\" Populated *****\n");
	}
	
	/**
	 * Returns a comma delimited String of the concatenated contents of a list
	 * @param list T
	 * @return
	 */
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
	
	/**
	 * adds information stored in "data" to the database
	 * @throws SQLException 
	 */
	public void addData() throws SQLException
	{
		int libID = 0;
		int pakID = 0;
		int typeID = 0;
		int methID = 0;
		
		// Insert an entry into the table
		PreparedStatement insertLib = conn.prepareStatement ("insert into Library (ID, Name)" +
														     "VALUES (?, ?)"
														 	);
		PreparedStatement insertPak = conn.prepareStatement ("insert into Package (ID, Name, LID)" +
															 "VALUES (?, ?, ?)"
															);
		PreparedStatement insertType = conn.prepareStatement ("insert into Type (ID, Name, PID, Source, IsInterface, IsInnerClass, Javadoc, Annotations, Modifiers, TypeParams, TypeParamBindings, SuperClass, Interfaces, DeclaringClass)" +
				  											  "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
															 );
		PreparedStatement insertMeth = conn.prepareStatement ("insert into Method (ID, Name, TID, Source, Constructor, Javadoc, Annotations, Modifiers, TypeParams, TypeParamBindings, ReturnType, Arguments, NumArguments, ArgumentTypes, ThrownExceptions, Body, DeclaringClass)" +
														  	  "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
															 );
		
		for(String lib: data.keySet())
		{
			
			insertLib.setInt(1, libID);
			insertLib.setString(2, lib);
			//insertLib.executeUpdate();
			insertLib.addBatch();
//			if (libID % batchMax == 0)
//			{
//				insertLib.executeBatch();
//				insertLib.clearBatch();
//			}
			for(String pak: data.get(lib).keySet())
			{
				insertPak.setInt(1, pakID);
				insertPak.setString(2, pak);
				insertPak.setInt(3, libID);
				//insertPak.executeUpdate();
				insertPak.addBatch();
//				if (pakID % batchMax == 0)
//				{
//					insertPak.executeBatch();
//					insertPak.clearBatch();
//				}					
				for(SourceParser parser: data.get(lib).get(pak))
				{
					for(ParsedType type: parser.getParsedTypes())
					{
						insertType.setInt(1, typeID);
						insertType.setString(2, type.getName());
						insertType.setInt(3, pakID);
						insertType.setString(4, type.getSource());
						insertType.setBoolean(5, type.isInterface());
						insertType.setBoolean(6, type.isInnerClass());
						insertType.setString(7, type.getJavadoc());
						insertType.setString(8, listToString(type.getAnnotations()));
						insertType.setString(9, listToString(type.getModifiers()));
						insertType.setString(10, listToString(type.getTypeParameters()));
						insertType.setString(11, listToString(type.getTypeParameterBindings()));
						insertType.setString(12, type.getSuperClass());
						insertType.setString(13, listToString(type.getInterfaces()));
						insertType.setString(14, type.getDeclaringClass());
						//insertType.executeUpdate();
						insertType.addBatch();
//						if (typeID % batchMax == 0)
//						{
//							insertType.executeBatch();
//							insertType.clearBatch();
//						}
						for(ParsedMethod meth: parser.getParsedMethods())
						{
							if (meth.getDeclaringClass().equals(type.getName()))
							{
								insertMeth.setInt(1, methID);
								insertMeth.setString(2, meth.getName());
								insertMeth.setInt(3, typeID);
								insertMeth.setString(4, type.getSource());
								insertMeth.setBoolean(5, meth.isConstructor());
								insertMeth.setString(6, meth.getJavadoc());
								insertMeth.setString(7, listToString(meth.getAnnotations()));
								insertMeth.setString(8, listToString(meth.getModifiers()));
								insertMeth.setString(9, listToString(meth.getTypeParameters()));
								insertMeth.setString(10, listToString(meth.getTypeParameterBindings()));
								insertMeth.setString(11, meth.getReturnType());
								insertMeth.setString(12, listToString(meth.getArguments()));
								insertMeth.setInt(13, meth.getNumArguments());
								insertMeth.setString(14, listToString(meth.getArgumentTypes()));
								insertMeth.setString(15, listToString(meth.getThrownExceptions()));
								insertMeth.setString(16, meth.getBody());
								insertMeth.setString(17, meth.getDeclaringClass());								
								//insertMeth.executeUpdate();
								insertMeth.addBatch();
								if (methID % batchMax == 0)
								{
									insertLib.executeBatch();
									insertPak.executeBatch();
									insertType.executeBatch();
									insertMeth.executeBatch();
									
									insertLib.clearBatch();
									insertPak.clearBatch();
									insertType.clearBatch();
									insertMeth.clearBatch();
								}
								methID++;
							}
						}
						typeID++;
					}
				}
				pakID++;
			}
			libID++;
		}
		insertLib.executeBatch();
		insertPak.executeBatch();
		insertType.executeBatch();
		insertMeth.executeBatch();
		conn.commit();
		
		int rows = libID + pakID + typeID + methID;
		System.out.println("***** Finished Adding Data *****");
		System.out.println("***** " + rows + " rows affected *****");
	}
	
	/**
	 * Creates tables in database
	 * @throws SQLException 
	 */
	public void buildDatabase() throws SQLException
	{
		if(conn == null)
		{
			System.err.println("Error: Not connected to a database.");
			return;
		}
		
		// Drop tables
		Statement drop = conn.createStatement();
		drop.executeUpdate("drop table Method");
		drop.executeUpdate("drop table Type");
		drop.executeUpdate("drop table Package");
		drop.executeUpdate("drop table Library");
		// not implemented
//		drop.executeUpdate("drop table Annotation");
//		drop.executeUpdate("drop table Modifier");
//		drop.executeUpdate("drop table TypeParams");
		drop.close();
		
		// Library Table
		
		// Create Table
		Statement create = conn.createStatement();
		create.executeUpdate("create table Library (" 	+
							 "ID int not null,"			+	//1
							 "Name text,"				+	//2
							 "primary key (ID) )" 
							);
		System.out.println("***** Created Table \"Library\" *****");
		
		// Package Table
		
		// Create Table
		create.executeUpdate("create table Package (" 	+
							 "ID int not null," 		+	//1
							 "Name text,"				+	//2
							 "LID int not null," 		+	//3
							 "primary key (ID), "		+
							 "foreign key (LID) references Library(ID) )" 
							);
		System.out.println("***** Created Table \"Package\" *****");
		
		
		// Type Table
		
		// Create Table
		create.executeUpdate("create table Type (" 		+
							 "ID int not null," 		+		//1
							 "Name text,"				+		//2
							 "PID int not null," 		+		//3
							 "Source longtext,"			+		//4
							 "IsInterface boolean," 	+		//5
							 "IsInnerClass boolean," 	+		//6
							 "Javadoc longtext," 		+		//7
							 "Annotations text," 		+		//8
							 "Modifiers text," 			+		//9
							 "TypeParams text," 		+		//10
							 "TypeParamBindings text," 	+		//11
							 "SuperClass text," 		+		//12
							 "Interfaces text," 		+		//13
							 "DeclaringClass text," 	+		//14
							 "primary key (ID), "		+
							 "foreign key (PID) references Package(ID) )"
							);
		
		System.out.println("***** Created Table \"Type\" *****");
		
		
		
		// Method Table

		// Create Table
		create.executeUpdate("create table Method ("+
							 "ID int not null,"			+	//1
							 "Name text,"				+	//2
							 "TID int not null,"		+	//3
							 "Source longtext,"			+	//4
							 "Constructor boolean," 	+	//5
							 "Javadoc longtext," 		+	//6
							 "Annotations text," 		+	//7
							 "Modifiers text," 			+	//8
							 "TypeParams text," 		+	//9
							 "TypeParamBindings text," 	+	//10
							 "ReturnType text," 		+	//11
							 "Arguments text," 			+	//12
							 "NumArguments int," 		+	//13
							 "ArgumentTypes text," 		+	//14
							 "ThrownExceptions text," 	+	//15
							 "Body longtext," 			+	//16
							 "DeclaringClass text," 	+	//17
							 "primary key (ID), "		+
							 "foreign key (TID) references Type(ID) )" 
							);		
		
		System.out.println("***** Created Table \"Method\" *****");
		
		
//		// Annotation Table
//
//		// Create Table
//		create.executeUpdate("create table Annotation (" +
//							 "ID int not null," +			//1
//							 "Name text,"		+			//2
//							 "primary key (ID) )" 
//							);
//		System.out.println("***** Created Table \"Annotation\" *****");
//		
//		// Modifier Table
//
//		// Create Table
//		create.executeUpdate("create table Modifier (" +
//							 "ID int not null," +			//1
//							 "Name text,"		+			//2
//							 "primary key (ID) )" 
//							);
//		System.out.println("***** Created Table \"Modifier\" *****");
//		
//		// TypeParams Table
//
//		// Create Table
//		create.executeUpdate("create table TypeParams (" +
//							 "ID int not null," +			//1
//							 "Name text,"		+			//2
//							 "primary key (ID) )" 
//							);
//		System.out.println("***** Created Table \"TypeParams\" *****");
		
		
		create.close();
	}
}
