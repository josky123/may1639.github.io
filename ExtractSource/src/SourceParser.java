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
	private boolean publicOnly = false;
	private boolean outerClassOnly = false;
	
	public SourceParser() {}
	
	public SourceParser(boolean publicOnly, boolean outerClassOnly) {
		this.publicOnly = publicOnly;
		this.outerClassOnly = outerClassOnly;
	}
	
	/**
	 * Sets the parser to ignore private methods and classes
	 * @param b
	 */
	public void setPublicOnly(boolean b) {
		publicOnly = b;
	}
	
	/**
	 * Sets the parser to ignore inner classes
	 * @param b
	 */
	public void setOuterClassOnly(boolean b) {
		outerClassOnly = b;
	}
	
	private String getSourceText(String path)
	{
		File sourceFile = new File(path);
		return getSourceText(sourceFile);
	}

	private String getSourceText(File sourceFile)
	{
		try {
			BufferedReader br = new BufferedReader(new FileReader(sourceFile));
			StringBuilder sb = new StringBuilder();
			String line;
			while ( (line = br.readLine()) != null) {
				sb.append(line + "\r\n");			
			}
			return sb.toString();
		} catch (Exception e) {
			e.printStackTrace();
		}
		return null;
	}
	
	private String getJavaDoc(MethodDeclaration m) {
		Javadoc jd = m.getJavadoc();
		String jDoc = "";
		if (jd != null)
			jDoc = jd.toString();
		return jDoc;
	}
	
	private List<String> getAnnotations(MethodDeclaration m) {
		ArrayList<IExtendedModifier> modifiers = new ArrayList<IExtendedModifier>();
		modifiers.addAll(m.modifiers());
		ArrayList<IExtendedModifier> anns = new ArrayList<IExtendedModifier>();
		if (!modifiers.isEmpty()) {
			for(IExtendedModifier iem: modifiers) {
				if (iem.isAnnotation())
					anns.add(iem);
			}
		}
		anns.trimToSize();
		ArrayList<String> annNames = new ArrayList<String>();
		if (!anns.isEmpty()) {
			for (IExtendedModifier iem: anns) {
				annNames.add(iem.toString());
			}
		}
		return annNames;
	}
	
	private List<String> getModifiers(MethodDeclaration m) {
		ArrayList<IExtendedModifier> modifiers = new ArrayList<IExtendedModifier>();
		modifiers.addAll(m.modifiers());
		ArrayList<IExtendedModifier> mods = new ArrayList<IExtendedModifier>();
		if (!modifiers.isEmpty()) {
			for(IExtendedModifier iem: modifiers) {
				if (iem.isModifier())
					mods.add(iem);
			}
		}
		mods.trimToSize();
		ArrayList<String> modNames = new ArrayList<String>();
		if (!mods.isEmpty()) {
			for (IExtendedModifier iem: mods) {
				modNames.add(iem.toString());
			}
		}
		return modNames;
	}
	
	private List<String> getTypeParameters(MethodDeclaration m) {
		ArrayList<TypeParameter> tp = new ArrayList<TypeParameter>();
		tp.addAll(m.typeParameters());
		ArrayList<String> typeNames = new ArrayList<String>();
		for (TypeParameter t: tp)
			typeNames.add(t.getName().toString());
		return typeNames;
	}
	
	private List<List<String>> getTypeParameterBindings(MethodDeclaration m) {
		ArrayList<TypeParameter> tp = new ArrayList<TypeParameter>();
		ArrayList<List<Type>> tpBounds = new ArrayList<List<Type>>();
		tp.addAll(m.typeParameters());
		for (TypeParameter temp: tp) {
			tpBounds.add(temp.typeBounds());
		}
		ArrayList<List<String>> bounds = new ArrayList<List<String>>();
		for (List<Type> temp: tpBounds)	{
			ArrayList<String> als = new ArrayList<String>();
			for (Type t: temp) {
				als.add(t.toString());
			}
			bounds.add(als);
		}
		return bounds;
	}
	
	private String getReturnType(MethodDeclaration m) {
		String retType;
		if (!m.isConstructor())
			retType = m.getReturnType2().toString();
		else
			retType = m.getName().toString();
		return retType;
	}
	
	private List<String> getArguments(MethodDeclaration m) {
		ArrayList<SingleVariableDeclaration> params = new ArrayList<SingleVariableDeclaration>();
		params.addAll(m.parameters());
		ArrayList<String> args = new ArrayList<String>();
		for (SingleVariableDeclaration svd: params)	{
			if(svd.isVarargs())
				args.add(svd.getType() + "... " + svd.getName());
			else
				args.add(svd.getType() + " " + svd.getName());
		}
		return args;
	}
	
	private List<String> getArgumentTypes(MethodDeclaration m) {
		ArrayList<SingleVariableDeclaration> params = new ArrayList<SingleVariableDeclaration>();
		params.addAll(m.parameters());
		ArrayList<String> args = new ArrayList<String>();
		for (SingleVariableDeclaration svd: params) {
			if(svd.isVarargs())
				args.add(svd.getType() + "[]");
			else
				args.add(svd.getType().toString());
		}
		return args;
	}
	
	private List<String> getThrownExceptions(MethodDeclaration m) {
		ArrayList<Name> exceptions = new ArrayList<Name>();
		exceptions.addAll(m.thrownExceptions());
		ArrayList<String> names = new ArrayList<String>();
		for (Name n: exceptions) {
			names.add(n.getFullyQualifiedName());
		}
		return names;
	}
	
	private String getContainingClass(MethodDeclaration m) {
		TypeDeclaration parent = (TypeDeclaration) m.getParent();
		return parent.getName().toString();
	}
	
	private String getOuterClass(MethodDeclaration m) {
		ASTNode root = m;
		while ( root.getParent().getParent() != null ) {
			root = root.getParent();
		}
		TypeDeclaration outerClass = (TypeDeclaration) root;
		return outerClass.getName().toString();
	}
	/**
	 * Creates a ParsedMethod object from a MethodDeclaration
	 * @param m
	 * 		MethodDeclaration
	 * @return
	 * 		ParsedMethod
	 */
	private ParsedMethod parseMethod(MethodDeclaration m) {
		ParsedMethod pm = new ParsedMethod();
		pm.setIsConstructor(m.isConstructor());
		pm.setJavadoc(getJavaDoc(m));
		pm.setAnnotations(getAnnotations(m));
		pm.setModifiers(getModifiers(m));
		pm.setTypeParameters(getTypeParameters(m));
		pm.setTypeParameterBindings(getTypeParameterBindings(m));
		pm.setReturnType(getReturnType(m));
		pm.setName(m.getName().toString());
		pm.setArguments(getArguments(m));
		pm.setArgumentTypes(getArgumentTypes(m));
		pm.setThrownExceptions(getThrownExceptions(m));
		pm.setBody(m.getBody().toString());
		pm.setSource(m.toString());
		pm.setContainingClass(getContainingClass(m));
		pm.setOuterClass(getOuterClass(m));
		return pm;
	}
	
	/**
	 * Creates a list of ParsedMethod objects from a list of MethodDeclaration objects
	 * @param m
	 * 		List of MethodDeclaration objects
	 * @return
	 * 		List of ParsedMethod objects
	 */
	private List<ParsedMethod> parseMethods(List<MethodDeclaration> m) {
		ArrayList<ParsedMethod> pms = new ArrayList<ParsedMethod>();
		for (MethodDeclaration temp: m) {
			
			ParsedMethod pm = parseMethod(temp);
			
			// hacked together conditionals
			if (publicOnly) {
				if ( Modifier.isPrivate( temp.getModifiers() ) ) {
					continue;
				}
			}
			if (outerClassOnly) { 
				if (pm.inInnerClass()) {
					continue;
				}
			}
			pms.add(pm);
		}
		return pms;
	}
	
	/**
	 * Parses a Java source file and returns a list of ParsedMethod objects
	 * @param path
	 * 		The path to the source file
	 * @return
	 * 		List of ParsedMethod objects
	 */
	public List<ParsedMethod> parse(String path) {
		String source = getSourceText(path);
		return parseSource(source);
	}
	
	/**
	 * Parses a Java source file and returns a list of ParsedMethod objects
	 * @param file
	 * 		The source file
	 * @return
	 * 		List of ParsedMethod objects
	 */
	public List<ParsedMethod> parse(File file) {
		String source = getSourceText(file);
		return parseSource(source);
	}
	
	/**
	 * Parses the source file and constructs the list of ParsedMethod objects
	 * @param source
	 * @return
	 */
	private List<ParsedMethod> parseSource(String source) {
		ASTParser parser = ASTParser.newParser(AST.JLS3);
		parser.setResolveBindings(true);
		parser.setSource(source.toCharArray());
		parser.setKind(ASTParser.K_COMPILATION_UNIT);
		final CompilationUnit cu = (CompilationUnit) parser.createAST(null);
		ArrayList<MethodDeclaration> methods = new ArrayList<MethodDeclaration>();
		
		cu.accept(new ASTVisitor() {
			public boolean visit(MethodDeclaration node) {
				methods.add(node);
				return false; // do not continue to avoid usage info
			}
		});
		return parseMethods(methods);
	}
}