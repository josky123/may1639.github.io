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
	private boolean ignorePrivate = false;
	private boolean outerClassOnly = false;
	
	private List<ParsedMethod> parsedMethods;
	private List<ParsedType> parsedTypes;
	private String filePackage;
	private String library;
	private File sourceFile;
	
	public SourceParser() {
		parsedMethods = new ArrayList<ParsedMethod>();
		parsedTypes = new ArrayList<ParsedType>();
	}
	
	public SourceParser(boolean ignorePrivate, boolean outerClassOnly) {
		this.ignorePrivate = ignorePrivate;
		this.outerClassOnly = outerClassOnly;
		parsedMethods = new ArrayList<ParsedMethod>();
		parsedTypes = new ArrayList<ParsedType>();
	}
	
	/**
	 * Sets the parser to ignore private methods and classes
	 * @param b
	 */
	public void setPublicOnly(boolean b) {
		ignorePrivate = b;
	}
	
	/**
	 * Sets the parser to ignore inner classes
	 * @param b
	 */
	public void setOuterClassOnly(boolean b) {
		outerClassOnly = b;
	}
	
	public List<ParsedMethod> getParsedMethods() {
		return parsedMethods;
	}
	
	public List<ParsedType> getParsedTypes() {
		return parsedTypes;
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
			br.close();
			return sb.toString();
		} catch (Exception e) {
			e.printStackTrace();
		}
		return null;
	}
	
	private void setLibraryAndPackage(String path)
	{
		File file = new File(path);
		setLibraryAndPackage(file);
	}
	
	private void setLibraryAndPackage(File file)
	{
		
	}
	
	private String getJavaDoc(BodyDeclaration m) {
		Javadoc jd = m.getJavadoc();
		String jDoc = "";
		if (jd != null)
			jDoc = jd.toString();
		return jDoc;
	}
	
	private List<String> getAnnotations(BodyDeclaration m) {
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
	
	private List<String> getModifiers(BodyDeclaration m) {
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
	
	private List<String> getTypeParameters(TypeDeclaration td) {
		ArrayList<TypeParameter> tp = new ArrayList<TypeParameter>();
		tp.addAll(td.typeParameters());
		ArrayList<String> typeNames = new ArrayList<String>();
		for (TypeParameter t: tp)
			typeNames.add(t.getName().toString());
		return typeNames;
	}
	
	private List<List<String>> getTypeParameterBindings(TypeDeclaration td) {
		ArrayList<TypeParameter> tp = new ArrayList<TypeParameter>();
		ArrayList<List<Type>> tpBounds = new ArrayList<List<Type>>();
		tp.addAll(td.typeParameters());
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
	
	private String getSuperclass(TypeDeclaration td) {
		Type t = td.getSuperclassType();
		if (t == null)
			return "null";
		else
			return t.toString();
	}
	
	private List<String> getSuperInterfaces(TypeDeclaration td) {
		ArrayList<Type> interfaces = new ArrayList<Type>();
		interfaces.addAll(td.superInterfaceTypes());
		ArrayList<String> names = new ArrayList<String>();
		for (Type t: interfaces) {
			names.add(t.toString());
		}
		return names;
	}
	
	private String getReturnType(MethodDeclaration m) {
		String retType;
		if ( !m.isConstructor())
		{
			if (m.getReturnType2() != null)
				retType = m.getReturnType2().toString();
			else
				retType = "void"; 
		}
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
		ASTNode parent = m.getParent();
		if(TypeDeclaration.class.isAssignableFrom(parent.getClass()))
			return ((TypeDeclaration) parent).getName().toString();
		else
			return "";
	}
	
	private String getContainingClass(TypeDeclaration td) {
		ASTNode p = td.getParent();
		if (p instanceof TypeDeclaration)
			return ((TypeDeclaration) p).getName().toString();
		else
			return "null";
	}
	
	private String getOuterClass(MethodDeclaration m) {
		ASTNode root = m;
		while ( root.getParent().getParent() != null ) {
			root = root.getParent();
		}
		TypeDeclaration outerClass = (TypeDeclaration) root;
		return outerClass.getName().toString();
	}
	
	private boolean isInnerClass(TypeDeclaration td) {
		ASTNode p = td.getParent();
		if (p instanceof TypeDeclaration)
			return true;
		else
			return false;
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
		if ( !(m.getBody() == null) )
			pm.setBody(m.getBody().toString());
		else
			pm.setBody("");
		pm.setSource(m.toString());
		pm.setDeclaringClass(getContainingClass(m));
		pm.setOuterClass(getOuterClass(m));
		
		// exclusion conditionals
		if (ignorePrivate) {
			if ( Modifier.isPrivate( m.getModifiers() ) ) {
				return null;
			}
		}
		if (outerClassOnly) { 
			if (pm.inInnerClass()) {
				return null;
			}
		}
		
		parsedMethods.add(pm);
		return pm;
	}
	
	private ParsedType parseType(TypeDeclaration t) {
		ParsedType pt = new ParsedType();
		pt.setIsInterface(t.isInterface());
		pt.setIsInnerClass(isInnerClass(t));
		pt.setJavadoc(getJavaDoc(t));
		pt.setAnnotations(getAnnotations(t));
		pt.setModifiers(getModifiers(t));
		pt.setName(t.getName().toString());
		pt.setTypeParameters(getTypeParameters(t));
		pt.setTypeParameterBindings(getTypeParameterBindings(t));
		pt.setSuperClass(getSuperclass(t));
		pt.setInterfaces(getSuperInterfaces(t));
//		pt.setBody();
		pt.setSource(t.toString());
		pt.setDeclaringClass(getContainingClass(t));
		
		// exclusion conditionals
		if (ignorePrivate) {
			if ( Modifier.isPrivate( t.getModifiers() ) ) {
				return null;
			}
		}
		if (outerClassOnly) { 
			if (pt.isInnerClass()) {
				return null;
			}
		}
		
		parsedTypes.add(pt);
		return pt;
	}
	
	/**
	 * Creates a list of ParsedMethod objects from a list of MethodDeclaration objects
	 * @param m
	 * 		List of MethodDeclaration objects
	 * @return
	 * 		List of ParsedMethod objects
	 */
	private void parseMethods(List<MethodDeclaration> m) {
		for (MethodDeclaration temp: m) {
			parseMethod(temp);
		}
	}
	
	private void parseTypes(List<TypeDeclaration> t) {
		for (TypeDeclaration temp: t) {
			parseType(temp);
		}
	}
	
	/**
	 * Parses a Java source file and returns a list of ParsedMethod objects
	 * @param path
	 * 		The path to the source file
	 * @return
	 * 		List of ParsedMethod objects
	 */
	public void parse(String path) {
		String source = getSourceText(path);
		sourceFile = new File(path);
		parseSource(source);
	}
	
	/**
	 * Parses a Java source file and returns a list of ParsedMethod objects
	 * @param file
	 * 		The source file
	 * @return
	 * 		List of ParsedMethod objects
	 */
	public void parse(File file) {
		String source = getSourceText(file);
		sourceFile = file;
		parseSource(source);
	}
	
	/**
	 * Parses the source file and constructs the list of ParsedMethod objects
	 * @param source
	 * @return
	 */
	private void parseSource(String source) {
		ASTParser parser = ASTParser.newParser(AST.JLS3);
		parser.setResolveBindings(true);
		parser.setSource(source.toCharArray());
		parser.setKind(ASTParser.K_COMPILATION_UNIT);
		final CompilationUnit cu = (CompilationUnit) parser.createAST(null);
		ArrayList<MethodDeclaration> methods = new ArrayList<MethodDeclaration>();
		ArrayList<TypeDeclaration> types = new ArrayList<TypeDeclaration>();
		
		cu.accept(new ASTVisitor() {
			public boolean visit(TypeDeclaration node) {
				types.add(node);
				return true;
			}
			
			public boolean visit(MethodDeclaration node) {
				methods.add(node);
				return false; // do not continue to avoid usage info
			}			
		});
		parseTypes(types);
		parseMethods(methods);
	}
}