import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

public class FileWalker {

	
	private HashMap<String, HashMap<String, ArrayList<SourceParser>>> libraries = new HashMap<String, HashMap<String, ArrayList<SourceParser>>>();
	
	private boolean ignorePrivate = false;
	private boolean outerClassOnly = false;
	
	public FileWalker(boolean ignorePrivate, boolean outerClassOnly) {
		this.ignorePrivate = ignorePrivate;
		this.outerClassOnly = outerClassOnly;
	}
	
	public HashMap<String, HashMap<String, ArrayList<SourceParser>>> getData() {
		return libraries;
	}
	
	public void extractAll(String s) {
		File f = new File(s);
		extractAll(f);
	}
	
	public void printData()
	{
		for (String lib: libraries.keySet())
		{
			System.out.println("Library " + lib + ":");
			for (String pak: libraries.get(lib).keySet())
			{
				System.out.println("\tPackage" + pak + ":");
				ArrayList<SourceParser> splist = libraries.get(lib).get(pak);
				//System.out.println("\t\tFile " + splist.get(0).getParsedTypes().get(0).getName() + ".java");
				for (SourceParser sp: splist)
				{
					System.out.println("\t\tTypes: ");
					for (ParsedType pt: sp.getParsedTypes())
					{
						System.out.println("\t\t\t" + pt.getName());
					}
					System.out.println("\t\tMethods: ");
					for (ParsedMethod pm: sp.getParsedMethods())
					{
						System.out.println("\t\t\t" + pm.getName());
					}
				}
			}
		}
	}
	
	public void extractAll(File f) {
		if (!f.isDirectory())
		{
			String path = f.toString();
			path = path.substring(path.indexOf("\\") + 1, path.lastIndexOf("\\"));
			
			// get library and package names
			String library = path.substring(0, path.indexOf("\\"));
			String fpackage = path.substring(path.indexOf("\\") + 1);
			fpackage = fpackage.replace("\\", ".");
			
			// parse java file
			SourceParser sp = new SourceParser(ignorePrivate, outerClassOnly);
			sp.parse(f);
			
			// add parser
			HashMap<String, ArrayList<SourceParser>> pak;
			ArrayList<SourceParser> list;
			if(libraries.containsKey(library))
			{
				pak = libraries.get(library);
				if(pak.containsKey(fpackage)) {
					list = pak.get(fpackage);
				}
				else {
					list = new ArrayList<SourceParser>();
				}
			}
			else
			{
				pak = new HashMap<String, ArrayList<SourceParser>>();
				list = new ArrayList<SourceParser>();
			}
			list.add(sp);
			pak.put(fpackage, list);
			libraries.put(library, pak);

			
//			System.out.println("Library: " + library);
//			System.out.println("Package: " + fpackage);
//			System.out.println("File: " + f.getName());
//			System.out.println();
		}
		else
		{
			File [] sub = f.listFiles();
			File current;
			for (int i = 0; i < sub.length; i++)
			{
				extractAll(sub[i]);
			}
		}
	}
	
//	public static void main(String[] args) {
//		File root = new File("javasource");
//		FileWalker fw = new FileWalker(true, false);
//		fw.extractAll(root);
//		fw.printData();
//	}

}
