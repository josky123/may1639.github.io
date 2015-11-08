
public class TestTypeParams<T>
{
	private T t;          

    public void set(T t) {
        this.t = t;
    }

    public T get() {
        return t;
    }

    public <U extends Number & Runnable> void inspect(U u){
        System.out.println("T: " + t.getClass().getName());
        System.out.println("U: " + u.getClass().getName());
    }
    
    public int add(boolean b, int ... args)
    {
    	int sum = 0;
    	for (int i = 0; i < args.length; i++)
    		sum += args[i];
		return sum;
    }

    public static void main(String[] args) {
    	TestTypeParams<Integer> integerBox = new TestTypeParams<Integer>();
        integerBox.set(new Integer(10));
        //integerBox.inspect("some text"); // error: this is still String!
        //integerBox.inspect(5.0);
    }
}