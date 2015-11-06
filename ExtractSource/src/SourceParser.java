import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.OutputStreamWriter;
import java.io.Writer;
import java.util.ArrayList;
import java.util.HashSet;
import java.util.Set;

import org.eclipse.jdt.core.*;
import org.eclipse.jdt.core.dom.*;

public class SourceParser 
{
	public static String getSourceText(String path)
	{
		File sourceFile = new File(path);
		return getSourceText(sourceFile);
	}

	public static String getSourceText(File sourceFile)
	{
		try {
			BufferedReader br = new BufferedReader(new FileReader(sourceFile));
			StringBuilder sb = new StringBuilder();
			String line;
			while ( (line = br.readLine()) != null)
			{
				sb.append(line + "\r\n");			
			}
			return sb.toString();
		} catch (Exception e) {
			e.printStackTrace();
		}
		return null;
	}
	
	
	
	public static void main (String [] args)
	{
		String source = getSourceText("javasource\\jdk1.8.0_66\\java\\util\\ArrayList.java");
		//System.out.println(source);
		ASTParser parser = ASTParser.newParser(AST.JLS3);
		parser.setResolveBindings(true);
		parser.setSource(source.toCharArray());
		parser.setKind(ASTParser.K_COMPILATION_UNIT);
		
		final CompilationUnit cu = (CompilationUnit) parser.createAST(null);
		
		ArrayList<MethodDeclaration> methods = new ArrayList<MethodDeclaration>();
		
		cu.accept(new ASTVisitor() {

			public boolean visit(MethodDeclaration node) {
//				SimpleName name = node.getName();
//				System.out.println("Declaration of '"+name+"' at line"+cu.getLineNumber(name.getStartPosition()));
				methods.add(node);
				return false; // do not continue to avoid usage info
			}
 
		});
		
		for (MethodDeclaration m: methods)
		{
//			String [] mods, params, exepts;
//			String ret, name;
//			String n = m.getName().getIdentifier();
//			System.out.println(n);
//			Type t = m.getReturnType2();
//			String r = "";
//			if (t != null)
//				r = t.toString();
//			System.out.println(r);
			

			try {
				String text = m.toString();
				Writer out;
				out = new BufferedWriter(new OutputStreamWriter(new FileOutputStream("extract\\ArrayList." + m.getName() + ".txt")));
				out.write(text);
				out.close();
				System.out.println(text + "\n");
			} catch (Exception e) {
				// TODO Auto-generated catch block
				e.printStackTrace();
			}
			
			
		}
	}
}


// "C:\\Users\\Arik\\Documents\\Workspace Mars\\ExtractSource\\javasource\\jdk1.8.0_66\\java\\util\\ArrayList.java"