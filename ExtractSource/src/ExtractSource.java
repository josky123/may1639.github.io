import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.OutputStreamWriter;
import java.io.Writer;
import java.lang.reflect.Method;
import java.lang.reflect.Parameter;
import java.util.ArrayList;
import java.util.Scanner;


public class ExtractSource 
{
	public static String getMethodDeclaration(Method m)
	{
		String s, cName, mName, rName, rType, rType2, params, s1, s2, s3;
		s = m.toString();
		mName = m.getName();
		cName = m.getDeclaringClass().getName();
		rName = m.getGenericReturnType().toString();
		rType = m.getReturnType().getName();
		params = getParameters(m);
		
		
		//System.out.println("mName: " + mName + ", cName: " + cName + ", rName: " + rName + ", rType: " + rType);
		System.out.println("Before: " + s);
		if (rName.contains("java."))
			rName = rName.substring(rName.lastIndexOf('.') + 1);
		if (rType.charAt(0) == '[')
			rType = rType.substring(2, rType.lastIndexOf(';'));
		
//		s1 = s.substring(0, s.indexOf(cName));
//		s2 = s.substring(s.indexOf(mName));
//		s = s1 + s2;
		
		s1 = s.substring(0, s.indexOf(rType));
		s2 = s.substring(s.indexOf(mName), s.indexOf('('));
		s3 = s.substring(s.indexOf(')') + 1);
		
		s = s1 + rName + " " + s2 + params + s3;
		
		System.out.println("After:  " + s + "\n");
//		System.out.println(m.getReturnType().toString());
//		System.out.println(m.getDeclaringClass().getName());
		return s;
	}
	
	public static String getParameters(Method m)
	{
		Parameter [] p = m.getParameters();
		String [] params = new String[p.length];
		for (int i = 0; i < p.length; i++)
		{
			String name = p[i].getName();
			//String type = p[i].getType().getName();
			String type = p[i].getParameterizedType().toString();
			params[i] = type + " " + name;
		}
		
		StringBuilder sb = new StringBuilder();
		sb.append('(');
		for (int i = 0; i < params.length; i++)
		{
			sb.append(params[i]);
			if (i < params.length - 1)
				sb.append(", ");
		}
		sb.append(')');
		return sb.toString();
	}
	
	public static void main (String [] args) throws IOException
	{
		//Class c = TestClass.class;
		Class c = ArrayList.class;

		String cName = c.getName();
		cName = cName.substring(cName.lastIndexOf('.') + 1);
		
		//File sourceFile = new File("src\\" + c.getName() + ".java");
		File sourceFile = new File("C:\\Users\\Arik\\Documents\\Workspace Mars\\ExtractSource\\javasource\\jdk1.8.0_66\\java\\util\\" + cName + ".java");

		BufferedReader br = new BufferedReader(new FileReader(sourceFile));
		
		StringBuilder sb = new StringBuilder();
		
		Method[] m = c.getDeclaredMethods();
		
		
		for (Method temp: m)
		{
			getMethodDeclaration(temp);
		}
		System.out.println();
		
		String line;
		while ( (line = br.readLine()) != null)
		{
			sb.append(line + "\r\n");			
		}
		
		
		String text = sb.toString();
//		
//		Scanner kb = new Scanner(text);
//		for (int i = 0; i < m.length; i++)
//		{
//			String s;
//			if (i == m.length - 1)
//				s = text.substring(text.indexOf(m[i].getName()));
//			else
//				s = text.substring(text.indexOf(m[i].getName()), text.indexOf(m[i+1].getName()));
//			String body = s.substring(0, s.lastIndexOf("}"));
//			System.out.println(body);
//		}
		
//		for (int i = 0; i < m.length; i++)
//		{
//			String s;
//			while (kb.hasNext())
//			{
//				s = kb.nextLine();
//				if (s.contains(s))
//				{
//					int start = text.substring(text.indexOf(s))
//				}
//			}
//		}
		System.out.println();
		System.out.println(text);
	
		Writer out = new BufferedWriter(new OutputStreamWriter(new FileOutputStream(c.getName() + ".txt")));
		out.write(text);
		out.close();

		
		
		
		
	}
}
