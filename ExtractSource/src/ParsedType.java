import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.OutputStreamWriter;
import java.io.Writer;
import java.util.Iterator;
import java.util.List;

public class ParsedType 
{
	/*
	 * TypeDeclaration:
                ClassDeclaration
                InterfaceDeclaration
	 ClassDeclaration:
	      [ Javadoc ] { ExtendedModifier } class Identifier
	                        [ < TypeParameter { , TypeParameter } > ]
	                        [ extends Type ]
	                        [ implements Type { , Type } ]
	                        { { ClassBodyDeclaration | ; } }
	 InterfaceDeclaration:
	      [ Javadoc ] { ExtendedModifier } interface Identifier
	                        [ < TypeParameter { , TypeParameter } > ]
	                        [ extends Type { , Type } ]
	                        { { InterfaceBodyDeclaration | ; } }

	 */
	
	/*
	 * Javadoc
	 * annotations
	 * modifiers
	 * "class" || "interface"
	 * name
	 * type parameters
	 * type parameter bindings
	 * superclass (extends Type)
	 * if class: implemented interfaces
	 * Class body || interface body
	 * 
	 * source
	 */
	
	private boolean isInterface;
	private boolean isInnerClass;
	
	private String javadoc;
	private List<String> annotations;
	private List<String> modifiers;
	private String name;
	private List<String> typeParameters;
	private List<List<String>> typeParameterBindings;
	private String superClass;
	private List<String> interfaces;
	//private String body;
	private String source;
	private String declaringClass;
	private String fpackage;
	private String library;
	
	// Getter Methods
	
	public boolean isInterface() {
		return isInterface;
	}
	
	public boolean isInnerClass() {
		return isInnerClass;
	}
	
	public String getJavadoc() {
		return javadoc;
	}
	
	public List<String> getAnnotations() {
		return annotations;
	}
	
	public List<String> getModifiers() {
		return modifiers;
	}
	
	public String getName() {
		return name;
	}
	
	public List<String> getTypeParameters() {
		return typeParameters;
	}
	
	public List<List<String>> getTypeParameterBindings() {
		return typeParameterBindings;
	}
	
	public String getSuperClass() {
		return superClass;
	}
	
	public List<String> getInterfaces() {
		return interfaces;
	}
	
//	public String getBody() {
//		return body;
//	}
	
	public String getSource() {
		return source;
	}
	
	public String getDeclaringClass() {
		return declaringClass;
	}
	
	public String getPackage() {
		return fpackage;
	}
	
	public String getLibrary() {
		return library;
	}
		
	// Setter Methods
	
	public void setIsInterface(boolean b) {
		isInterface = b;
	}
	
	public void setIsInnerClass(boolean b) {
		isInnerClass = b;
	}
	
	public void setJavadoc(String s) {
		javadoc = s;
	}
	
	public void setAnnotations(List<String> s) {
		annotations = s;
	}
	
	public void setModifiers(List<String> s) {
		modifiers = s;
	}
	
	public void setName(String s) {
		name = s;
	}
	
	public void setTypeParameters(List<String> s) {
		typeParameters = s;
	}
	
	public void setTypeParameterBindings(List<List<String>> s) {
		typeParameterBindings = s;
	}
	
	public void setSuperClass(String s) {
		superClass = s;
	}
	
	public void setInterfaces(List<String> s) {
		interfaces = s;
	}
	
//	public void setBody(String s) {
//		body = s;
//	}
	
	public void setSource(String s) {
		source = s;
	}
	
	public void setDeclaringClass(String s) {
		declaringClass = s;
	}
	
	public void setPackage(String s) {
		fpackage = s;
	}
	
	public void setLibrary(String s) {
		library = s;
	}
	
	// do stuff
	
	public String getFileName() {
		StringBuilder fName = new StringBuilder();
		if (this.isInnerClass)
		{
			if (!declaringClass.equals("null"))
				fName.append(superClass + "." + name + ".txt");
			else
				fName.append(name + ".txt");
		}
		else
			fName.append(name + ".txt");
		return fName.toString();
	}
	
	public File getFile() {
		return new File(this.getFileName());
	}
	
	public void printToFile() {
		File file = this.getFile();
		try {
			Writer out;
			out = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(file)));
			out.write(source);
			out.close();
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
	
	public void printToFile(String fName) {
		File file = new File(fName);
		try {
			Writer out;
			out = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(file)));
			out.write(source);
			out.close();
		} catch (Exception e) {
			// TODO Auto-generated catch block
			e.printStackTrace();
		}
	}
}
