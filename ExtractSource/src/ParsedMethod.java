import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.OutputStreamWriter;
import java.io.Writer;
import java.util.Iterator;
import java.util.List;

public class ParsedMethod 
{
	private boolean constructor;
	
	private String javadoc;
	private List<String> annotations;
	private List<String> modifiers;
	private List<String> typeParameters;
	private List<List<String>> typeParameterBindings;
	private String returnType;
	private String name;
	private List<String> arguments;
	private int numArguments;
	private List<String> argumentTypes;
	private List<String> thrownExceptions;
	private String body;
	private String source;
	private String containingClass;
	private String outerClass;
	
	public ParsedMethod() {}
	
	// Getter Methods
	
	public boolean isConstructor() {
		return constructor;
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
	
	public List<String> getTypeParameters() {
		return typeParameters;
	}
	
	public List<List<String>> getTypeParameterBindings() {
		return typeParameterBindings;
	}
	
	public String getReturnType() {
		return returnType;
	}
	
	public String getName() {
		return name;
	}
	
	public List<String> getArguments() {
		return arguments;
	}
	
	public int getNumArguments() {
		return numArguments;
	}
	
	public List<String> getArgumentTypes() {
		return argumentTypes;
	}
	
	public List<String> getThrownExceptions() {
		return thrownExceptions;
	}
	
	public String getBody() {
		return body;
	}
	
	public String getSource() {
		return source;
	}
	
	public String getContainingClass() {
		return containingClass;
	}
	
	public String getOuterClass() {
		return outerClass;
	}
	
	// Setter Methods
	
	public void setIsConstructor(boolean b) {
		constructor = b;
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
	
	public void setTypeParameters(List<String> s) {
		typeParameters = s;
	}
	
	public void setTypeParameterBindings(List<List<String>> s) {
		typeParameterBindings = s;
	}
	
	public void setReturnType(String s) {
		returnType = s;
	}
	
	public void setName(String s) {
		name = s;
	}
	
	public void setArguments(List<String> s) {
		arguments = s;
		numArguments = arguments.size();
	}
	
	public void setArgumentTypes(List<String> s) {
		argumentTypes = s;
	}
	
	public void setThrownExceptions(List<String> s) {
		thrownExceptions = s;
	}
	
	public void setBody(String s) {
		body = s;
	}
	
	public void setSource(String s) {
		source = s;
	}
	
	public void setContainingClass(String s) {
		containingClass = s;
	}
	
	public void setOuterClass(String s) {
		outerClass = s;
	}
	
	// do stuff
	
	public boolean inInnerClass() {
		if (containingClass.equals(outerClass))
			return false;
		return true;
	}
	
	public String getFileName() {
		StringBuilder fName = new StringBuilder();
		if (this.inInnerClass())
			fName.append(outerClass + "." + containingClass + "." + name + "(");
		else
			fName.append(containingClass + "." + name + "(");
		Iterator it = arguments.iterator();
		while(it.hasNext())
		{
			fName.append(it.next());
			if (it.hasNext())
				fName.append(", ");
		}
		fName.append(").txt");
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