public class TestClass 
{
	private int stuff;
	
	public TestClass(int newStuff) {
		stuff = newStuff;
	}
	
	public int getStuff() {
		return stuff;
	}
	
	public void setStuff(int things) {
		stuff = things;
	}
	
	public void doStuff() {
		System.out.println("Stuff: " + stuff);
	}
}