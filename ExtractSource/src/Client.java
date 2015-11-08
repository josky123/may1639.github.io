import java.util.ArrayList;

public class Client 
{
	public static void main(String [] args)
	{
		SourceParser sp = new SourceParser(true, true);
		ArrayList<ParsedMethod> pms = (ArrayList<ParsedMethod>) sp.parse("javasource\\jdk1.8.0_66\\java\\util\\ArrayList.java");
		for (ParsedMethod p: pms)
		{
			p.printToFile("extract\\" + p.getFileName());
		}
	}
}
