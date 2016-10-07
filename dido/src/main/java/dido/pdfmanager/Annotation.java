package dido.pdfmanager;

public class Annotation {
	private String content=null;
	private String author=null;
	private String type=null;
	private String created=null;
	private String modified=null;
	public String getContent() {
		return content;
	}
	public void setContent(String content) {
		this.content = content;
	}
	public String getAuthor() {
		return author;
	}
	public void setAuthor(String author) {
		this.author = author;
	}
	public String getType() {
		return type;
	}
	public void setType(String type) {
		this.type = type;
	}
	public String getModified() {
		return modified;
	}
	public void setModified(String modified) {
		this.modified = modified;
	}
	public String getCreated() {
		return created;
	}
	public void setCreated(String created) {
		this.created = created;
	}
	public boolean isEmpty(){
		return (content==null&&author==null&&type==null&&modified==null&&created==null);
		
	} 
	}
