import java.io.File;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class ExtractJava 
{
	private HashMap<String, HashMap<String, ArrayList<SourceParser>>> libraries = new HashMap<String, HashMap<String, ArrayList<SourceParser>>>();
	
	private boolean ignorePrivate = false;
	private boolean outerClassOnly = false;
	private String root;
	
	public ExtractJava(String root, boolean ignorePrivate, boolean outerClassOnly) {
		this.root = root;
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
	
	public void extractAll()
	{
		extractAll(root);
	}
	
	public void extractAll(File f) {
		if (!f.isDirectory())
		{
			//String path = f.toString();
			//path = path.substring(path.indexOf("\\") + 1, path.lastIndexOf("\\"));
			
			// get library and package names
//			String library = path.substring(0, path.indexOf("\\"));
			String library = root;
			if (library.contains("\\"))
			{
				library = library.substring(library.lastIndexOf('\\') + 1);
			}
//			String fpackage = path.substring(path.indexOf("\\") + 1);
//			fpackage = fpackage.replace("\\", ".");
			
			
			
			// parse java file
			SourceParser sp = new SourceParser(ignorePrivate, outerClassOnly);
			sp.parse(f);
			
			String text = sp.getSourceText(f);
			Pattern p = Pattern.compile("package [\\w.]*\\.[\\w.]*;");
			Matcher m = p.matcher(text);
			boolean b = m.find();
			String fpackage;
			if (b){
				fpackage = m.group();
				fpackage = fpackage.substring("package ".length(), fpackage.length()-1);
			}
			else
				fpackage = "default";

			
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
			for (int i = 0; i < sub.length; i++)
			{
				extractAll(sub[i]);
			}
		}
	}
	
	public void printData()
	{
		for (String lib: libraries.keySet())
		{
			System.out.println("Library " + lib + ":");
			for (String pak: libraries.get(lib).keySet())
			{
				System.out.println("\tPackage " + pak + ":");
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
}
