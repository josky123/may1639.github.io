import java.io.File;
import java.sql.SQLException;
import java.util.ArrayList;

public class Client 
{	
	public static void main(String [] args) throws SQLException
	{
		//SourceParser sp = new SourceParser(true, true);
//		SourceParser sp = new SourceParser(false, false);
//		sp.parse("javasource\\jdk1.8.0_66\\java\\util\\ArrayList.java");
//		ArrayList<ParsedMethod> pms = (ArrayList<ParsedMethod>) sp.getParsedMethods();
//		ArrayList<ParsedType> pts = (ArrayList<ParsedType>) sp.getParsedTypes();
		
		// write source to file
//		for (ParsedMethod p: pms)
//		{
//			p.printToFile("extract\\" + p.getFileName());
//		}
		
		// print modifiers + file name
//		for (ParsedMethod p: pms)
//		{
//			//System.out.println(p.getModifiers() + " " + p.getFileName());
//			System.out.println(p.getTypeParameterBindings());
//		}

		// print class names + isInnerClass
//		for (ParsedType pt: pts) {
//			System.out.println(pt.getName() + " " + pt.isInnerClass());
//		}
		
//		for (ParsedType pt: pts) {
//			System.out.println(pt.getFileName());
//		}
		
		//File root = new File("javasource");
//		FileWalker fw = new FileWalker(true, false);
//		fw.extractAll(root);
//		//fw.printData();
		
		//System.out.println(args[0]);
		ExtractJava ej = new ExtractJava(args[0], true, false);
		ej.extractAll();
		ej.printData();
		
		
//		DatabaseManager dm = new DatabaseManager(ej.getData());
//		dm.connect();
//		dm.buildDatabase();
//		dm.addData();
//		dm.close();
		
		
		
//		DatabaseManager dm = new DatabaseManager(pms, pts);
//		dm.connect();
//		dm.createMethodsTable();
//		dm.createTypesTable();
//		dm.close();
		
		
	}
}
