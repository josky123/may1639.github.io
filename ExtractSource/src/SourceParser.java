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
import java.util.List;
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
		
		/*
		 * check if method or constructor
		 * 
		 * if method:
		 * 
		 * need to get:
		 * javadoc			m.getJavadoc().toString();
		 * annotations
		 * modifiers
		 * type parameters
		 * return type
		 * name
		 * arguments (parameters)
		 * thrown exceptions
		 * body
		 * 
		 * if constructor:
		 * 
		 * need to get:
		 * javadoc
		 * annotations
		 * modifiers
		 * type parameters
		 * name
		 * arguments (parameters)
		 * thrown exceptions
		 * body
		 */
		
		for (MethodDeclaration m: methods)
		{		
			// get javadoc
			Javadoc jd = m.getJavadoc();
			String jDoc = "";
			if (jd != null)
				jDoc = jd.toString();
			//System.out.println(jDoc);
			
			
			// get annotations and modifiers
			ArrayList<IExtendedModifier> modifiers = new ArrayList<IExtendedModifier>();
			modifiers.addAll(m.modifiers());
			ArrayList<IExtendedModifier> mods = new ArrayList<IExtendedModifier>();
			ArrayList<IExtendedModifier> anns = new ArrayList<IExtendedModifier>();
			if (!modifiers.isEmpty())
			{
				for(IExtendedModifier iem: modifiers)
				{
					if (iem.isAnnotation())
						anns.add(iem);
					else if (iem.isModifier())
						mods.add(iem);
				}
			}
			anns.trimToSize();
			mods.trimToSize();
			
			// get names of annotations and modifiers
			ArrayList<String> annNames = new ArrayList<String>();
			ArrayList<String> modNames = new ArrayList<String>();		
			if (!anns.isEmpty())
			{
				for (IExtendedModifier iem: anns)
				{
					annNames.add(iem.toString());
				}
			}
			if (!mods.isEmpty())
			{
				for (IExtendedModifier iem: mods)
				{
					modNames.add(iem.toString());
				}
			}
			
//			if (!annNames.isEmpty())
//				System.out.println(annNames.toString());
//			if (!modNames.isEmpty())
//				System.out.println(modNames.toString());
			
			// get type parameters
			ArrayList<TypeParameter> tp = new ArrayList<TypeParameter>();
			tp.addAll(m.typeParameters());
//			if (!tp.isEmpty())
//				System.out.println(tp.get(0).toString());
			
//			try {
				String text = m.toString();
//				Writer out;
//				out = new BufferedWriter(new OutputStreamWriter(new FileOutputStream("extract\\ArrayList." + m.getName() + ".txt")));
//				out.write(text);
//				out.close();
				System.out.println(text + "\n");
//			} catch (Exception e) {
//				// TODO Auto-generated catch block
//				e.printStackTrace();
//			}
			
			
		}
	}
}


// "C:\\Users\\Arik\\Documents\\Workspace Mars\\ExtractSource\\javasource\\jdk1.8.0_66\\java\\util\\ArrayList.java"